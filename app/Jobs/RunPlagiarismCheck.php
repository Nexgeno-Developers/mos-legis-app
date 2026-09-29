<?php

namespace App\Jobs;

use App\Enums\PlagiarismCheckStatus;
use App\Enums\PlagiarismCheckType;
use App\Models\PlagiarismCheck;
use App\Notifications\WorkflowNotifier;
use App\Services\Documents\DocumentRenderer;
use App\Services\Manuscripts\DocxWordCounter;
use App\Services\Manuscripts\ManuscriptWorkflow;
use App\Services\Plagiarism\PlagiarismChecker;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Sends manuscript or standalone content to the plagiarism checker, stores the
 * result and report, and applies the manuscript stage rules (SOW A.16/A.18).
 */
class RunPlagiarismCheck implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public PlagiarismCheck $check) {}

    public function handle(PlagiarismChecker $checker, DocxWordCounter $docx, ManuscriptWorkflow $workflow, DocumentRenderer $documents, WorkflowNotifier $notifier): void
    {
        $check = $this->check->fresh();
        if (! $check || $check->isCompleted()) {
            return;
        }

        $check->update(['check_status' => PlagiarismCheckStatus::Processing]);

        $text = $check->uploaded_file
            ? $docx->text(Storage::disk('local')->path($check->uploaded_file))
            : (string) $check->content;

        $result = $checker->check($check->title, $text);

        DB::transaction(function () use ($check, $result, $documents, $workflow) {
            $check->update([
                'similarity_percentage' => $result->similarity,
                'api_response' => $result->raw + ['matches' => $result->matches],
                'check_status' => PlagiarismCheckStatus::Completed,
                'checked_at' => now(),
            ]);
            $check->update(['report_file' => $documents->storePlagiarismReport($check)]);

            activity()->log('Plagiarism Checks', 'Check completed', $check, ['similarity' => $result->similarity]);

            if ($check->check_type === PlagiarismCheckType::Manuscript && $check->submission) {
                $workflow->applyPlagiarismResult($check->submission, $result->similarity);
            }
        });

        if ($check->check_type === PlagiarismCheckType::Standalone) {
            $notifier->toUser('plagiarism_check_completed', $check->user, ['title' => $check->title, 'similarity' => $result->similarity]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->check->fresh()?->update(['check_status' => PlagiarismCheckStatus::Failed]);
        activity()->log('Plagiarism Checks', 'Check failed', $this->check, [], $exception?->getMessage());
    }
}
