<?php

namespace App\Actions\Awards;

use App\Enums\AwardPeriodType;
use App\Enums\ManuscriptStage;
use App\Models\BestPaperAward;
use App\Models\ManuscriptSubmission;
use App\Models\User;
use App\Notifications\WorkflowNotifier;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;

/**
 * Creates or updates a Best Paper award: one winner per quarter, published manuscripts only.
 * The winning author is notified when they become the winner.
 * Used by Admin → Best Paper Awards and by the manuscript page.
 */
class SaveBestPaperAward
{
    public function __construct(private readonly WorkflowNotifier $notifier) {}

    /** @param array<string, mixed> $data validated award data (manuscript_submission_id, award_quarter, award_year, prize_amount, editorial_citation) */
    public function handle(array $data, User $actor, ?BestPaperAward $award = null): BestPaperAward
    {
        $submission = ManuscriptSubmission::findOrFail($data['manuscript_submission_id']);

        if ($submission->stage !== ManuscriptStage::Published) {
            throw ValidationException::withMessages(['manuscript_submission_id' => 'Only published manuscripts can win Best Paper.']);
        }

        $period = [
            'period_type' => AwardPeriodType::Quarterly,
            'award_month' => null,
            'award_quarter' => $data['award_quarter'],
            'award_year' => (int) $data['award_year'],
        ];

        $taken = BestPaperAward::where($period)->when($award, fn ($q) => $q->whereKeyNot($award->id))->first();
        if ($taken) {
            throw ValidationException::withMessages([
                'award_quarter' => "A Best Paper winner has already been chosen for {$taken->periodLabel()}.",
            ]);
        }

        $isNewWinner = ! $award || $award->manuscript_submission_id !== $submission->id;
        $award ??= new BestPaperAward(['selected_at' => today(), 'selected_by' => $actor->id]);

        try {
            $award->fill($period + [
                'manuscript_submission_id' => $submission->id,
                'prize_amount' => filled($data['prize_amount'] ?? null) ? $data['prize_amount'] : null,
                'editorial_citation' => $data['editorial_citation'],
            ])->save();
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['award_quarter' => 'A Best Paper winner has already been chosen for that quarter.']);
        }

        activity()->log('Submissions', $isNewWinner ? 'Marked as Best Paper Winner' : 'Best Paper award updated', $submission, ['period' => $award->periodLabel()]);

        if ($isNewWinner) {
            $this->notifier->toUser('best_paper_selected', $submission->author, [
                'reference' => $submission->reference(), 'title' => $submission->title, 'period' => $award->periodLabel(),
            ]);
        }

        return $award;
    }

    public function delete(BestPaperAward $award): void
    {
        activity()->log('Submissions', 'Best Paper award removed', $award->submission, ['period' => $award->periodLabel()]);
        $award->delete();
    }
}
