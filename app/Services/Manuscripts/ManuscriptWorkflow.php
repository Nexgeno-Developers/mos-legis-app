<?php

namespace App\Services\Manuscripts;

use App\Enums\ManuscriptStage;
use App\Enums\PlagiarismCheckStatus;
use App\Enums\PlagiarismCheckType;
use App\Enums\RevisionDecision;
use App\Enums\RoleName;
use App\Jobs\RunPlagiarismCheck;
use App\Models\ManuscriptRevision;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Models\PlagiarismCheck;
use App\Models\User;
use App\Notifications\WorkflowNotifier;
use App\Services\Documents\CertificateGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The SOW A.16 manuscript pipeline. Every transition records the stage change
 * time, writes the activity log and notifies author, superadmins and reviewer.
 */
class ManuscriptWorkflow
{
    public function __construct(
        private readonly WorkflowNotifier $notifier,
        private readonly ReviewerAllocator $allocator,
        private readonly FeeCalculator $fees,
        private readonly CertificateGenerator $certificates,
    ) {}

    /** A new manuscript has been lodged (stage: pending). */
    public function submitted(ManuscriptSubmission $submission): void
    {
        $submission->forceFill(['stage_changed_at' => now()])->save();

        activity()->log('Submissions', 'Manuscript submitted', $submission, ['title' => $submission->title]);
        $this->notifyParties('submission_received', $submission, reviewer: false);

        // With payments switched off (Settings → Payment) screening starts immediately.
        if (! $this->fees->paymentsEnabled()) {
            $this->startPlagiarismCheck($submission);
        }
    }

    /** Queue a manuscript plagiarism check (after the pre-screening fee is paid, or on admin re-check). */
    public function startPlagiarismCheck(ManuscriptSubmission $submission, ?Payment $payment = null): PlagiarismCheck
    {
        $payment ??= $submission->prescreeningPayment;

        $check = PlagiarismCheck::create([
            'user_id' => $submission->user_id,
            'manuscript_submission_id' => $submission->id,
            'check_type' => PlagiarismCheckType::Manuscript,
            'title' => $submission->title,
            'uploaded_file' => $submission->manuscript_attachment,
            'check_status' => PlagiarismCheckStatus::Pending,
            'payment_id' => $payment?->id,
        ]);

        RunPlagiarismCheck::dispatch($check)->afterCommit();

        return $check;
    }

    /**
     * Apply a completed plagiarism result: above the threshold → rejected;
     * at or below (10% counts as accepted) → plagiarism_accepted and auto-assignment.
     * Once a reviewer is involved, a re-check only updates the similarity figure.
     */
    public function applyPlagiarismResult(ManuscriptSubmission $submission, float $similarity): void
    {
        $submission->forceFill(['plagiarism_similarity' => $similarity])->save();

        $screeningStages = [ManuscriptStage::Pending, ManuscriptStage::PlagiarismAccepted, ManuscriptStage::Rejected];
        if (! in_array($submission->stage, $screeningStages, true) || $submission->revisions()->exists()) {
            return;
        }

        $threshold = settings()->float('manuscript.plagiarism_max_similarity_percent');

        if ($similarity > $threshold) {
            $this->transition($submission, ManuscriptStage::Rejected, "Similarity {$similarity}% above {$threshold}% threshold");
            $submission->prescreeningPayment?->update(['remarks' => "Similarity {$similarity}% — above {$threshold}% threshold"]);
            $this->notifyParties('submission_plagiarism_rejected', $submission, ['similarity' => $similarity, 'threshold' => $threshold], reviewer: false);

            return;
        }

        if ($submission->stage !== ManuscriptStage::PlagiarismAccepted) {
            $this->transition($submission, ManuscriptStage::PlagiarismAccepted, "Similarity {$similarity}%");
            $this->notifyParties('submission_plagiarism_accepted', $submission, ['similarity' => $similarity, 'threshold' => $threshold], reviewer: false);
        }

        if (! $submission->assigned_to && settings()->bool('manuscript.auto_assign_reviewer_enabled')) {
            $this->autoAssign($submission);
        }
    }

    public function autoAssign(ManuscriptSubmission $submission): ?User
    {
        $reviewer = $this->allocator->pick($submission->content_category_id);

        if (! $reviewer) {
            activity()->log('Submissions', 'No eligible reviewer — pending assignment', $submission);
            $this->notifier->toAdmins('submission_unassigned', $this->placeholders($submission));

            return null;
        }

        $this->assign($submission, $reviewer);

        return $reviewer;
    }

    /** Manual (superadmin) or automatic assignment; only one reviewer at a time. */
    public function assign(ManuscriptSubmission $submission, User $reviewer, ?User $actor = null): void
    {
        if (! $reviewer->isActive() || ! $reviewer->hasRole(RoleName::Reviewer->value)) {
            throw ValidationException::withMessages(['reviewer_id' => 'Choose an active reviewer.']);
        }

        $assignable = [ManuscriptStage::PlagiarismAccepted, ManuscriptStage::InReview, ManuscriptStage::Revision, ManuscriptStage::Resubmitted];
        if (! in_array($submission->stage, $assignable, true)) {
            throw ValidationException::withMessages(['reviewer_id' => 'A reviewer can be assigned once the manuscript has passed plagiarism screening.']);
        }

        DB::transaction(function () use ($submission, $reviewer, $actor) {
            $previous = $submission->assigned_to;
            $submission->forceFill(['assigned_to' => $reviewer->id, 'assigned_at' => now()])->save();

            if ($submission->stage === ManuscriptStage::PlagiarismAccepted) {
                $this->transition($submission, ManuscriptStage::InReview);
            }

            activity()->log('Submissions', $previous ? 'Reviewer reassigned' : ($actor ? 'Reviewer assigned' : 'Reviewer auto-assigned'), $submission, [
                'reviewer_id' => $reviewer->id, 'previous_reviewer_id' => $previous,
            ]);
        });

        $this->notifyParties('submission_assigned', $submission->fresh('reviewer'), ['reviewer_name' => $reviewer->name]);
    }

    /** Reviewer (or superadmin) decision on a manuscript in review / resubmitted. */
    public function decide(ManuscriptSubmission $submission, User $reviewer, RevisionDecision $decision, ?string $remarks): ManuscriptRevision
    {
        if (! $submission->stage->awaitsReviewerDecision()) {
            throw ValidationException::withMessages(['decision' => 'This manuscript is not awaiting a review decision.']);
        }

        $revision = DB::transaction(function () use ($submission, $reviewer, $decision, $remarks) {
            $revision = $submission->revisions()->create([
                'round' => (int) $submission->revisions()->max('round') + 1,
                'reviewer_id' => $reviewer->id,
                'decision' => $decision,
                'reviewer_remarks' => $remarks,
                'reviewed_attachment' => $submission->manuscript_attachment,
                'decided_at' => now(),
            ]);

            $this->transition($submission, match ($decision) {
                RevisionDecision::Approved => ManuscriptStage::Approved,
                RevisionDecision::Revision => ManuscriptStage::Revision,
                RevisionDecision::Rejected => ManuscriptStage::Rejected,
            }, $remarks);

            return $revision;
        });

        $data = ['remarks' => $remarks ?: '—'];

        if ($decision === RevisionDecision::Approved) {
            $fee = $this->fees->publicationFeeFor($submission) ?? 0.0;
            $data['amount'] = money($fee);

            // Nothing to pay (zero fee or payments switched off) → publish straight away.
            if ($fee <= 0 || ! $this->fees->paymentsEnabled()) {
                $this->notifyParties('submission_approved', $submission, $data);
                $this->publish($submission);

                return $revision;
            }
        }

        $this->notifyParties(match ($decision) {
            RevisionDecision::Approved => 'submission_approved',
            RevisionDecision::Revision => 'submission_revision_requested',
            RevisionDecision::Rejected => 'submission_rejected',
        }, $submission, $data);

        return $revision;
    }

    /** Author uploads the revised manuscript. */
    public function resubmit(ManuscriptSubmission $submission, string $path, int $wordCount, ?string $response): void
    {
        if ($submission->stage !== ManuscriptStage::Revision) {
            throw ValidationException::withMessages(['manuscript' => 'This manuscript is not awaiting a revision.']);
        }

        DB::transaction(function () use ($submission, $path, $wordCount, $response) {
            $submission->latestRevision?->update([
                'resubmitted_attachment' => $path,
                'resubmitted_word_count' => $wordCount,
                'author_response' => $response,
                'resubmitted_at' => now(),
            ]);

            $submission->forceFill(['manuscript_attachment' => $path, 'word_count' => $wordCount])->save();
            $this->transition($submission, ManuscriptStage::Resubmitted);
        });

        $this->notifyParties('submission_resubmitted', $submission, author: false);
    }

    /** Publication fee paid (or waived): publish and issue the certificate (clarification #1). */
    public function publish(ManuscriptSubmission $submission): void
    {
        if ($submission->stage === ManuscriptStage::Published) {
            return;
        }

        DB::transaction(function () use ($submission) {
            $submission->forceFill(['published_at' => now()])->save();
            $this->transition($submission, ManuscriptStage::Published);
        });

        if (settings()->bool('manuscript.manuscript_certificate_enabled')) {
            $this->certificates->issue($submission);
        }

        $this->notifyParties('submission_published', $submission);
    }

    /** Superadmin "Change Stage" override (SOW A.16). */
    public function changeStage(ManuscriptSubmission $submission, ManuscriptStage $stage, User $actor, ?string $remarks = null): void
    {
        if ($stage === $submission->stage) {
            return;
        }

        if ($stage === ManuscriptStage::Published) {
            $this->publish($submission);

            return;
        }

        $this->transition($submission, $stage, $remarks ?: 'Changed by '.$actor->name);
        $this->notifyParties('submission_stage_changed', $submission, ['stage' => $stage->label()]);
    }

    private function transition(ManuscriptSubmission $submission, ManuscriptStage $stage, ?string $remarks = null): void
    {
        $from = $submission->stage;

        $submission->forceFill(['stage' => $stage, 'stage_changed_at' => now()])->save();

        activity()->log('Submissions', "Stage changed to {$stage->value}", $submission, ['from' => $from?->value, 'to' => $stage->value], $remarks);
    }

    private function notifyParties(string $template, ManuscriptSubmission $submission, array $extra = [], bool $author = true, bool $reviewer = true): void
    {
        $data = $this->placeholders($submission) + $extra;

        if ($author) {
            $this->notifier->toUser($template, $submission->author, $data);
        }
        if ($reviewer && $submission->reviewer) {
            $this->notifier->toUser($template, $submission->reviewer, $data);
        }
        $this->notifier->toAdmins($template, $data);
    }

    /** @return array<string, scalar|null> */
    private function placeholders(ManuscriptSubmission $submission): array
    {
        $submission->loadMissing(['contentCategory:id,name', 'reviewer', 'author']);

        return [
            'reference' => $submission->reference(),
            'title' => $submission->title,
            'stage' => $submission->stage->label(),
            'content_category' => $submission->contentCategory?->name,
            'reviewer_name' => $submission->reviewer?->name,
            'similarity' => $submission->plagiarism_similarity,
        ];
    }
}
