<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Awards\SaveBestPaperAward;
use App\Enums\ManuscriptStage;
use App\Enums\RevisionDecision;
use App\Http\Controllers\Controller;
use App\Models\BestPaperAward;
use App\Models\ManuscriptSubmission;
use App\Models\User;
use App\Services\Manuscripts\ManuscriptWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * SOW A.16 actions on a manuscript: assign reviewer, review decision,
 * change stage, re-check plagiarism, and Best Paper Winner selection.
 */
class SubmissionWorkflowController extends Controller
{
    public function __construct(private readonly ManuscriptWorkflow $workflow) {}

    public function assign(Request $request, ManuscriptSubmission $submission): RedirectResponse
    {
        Gate::authorize('assign', $submission);

        if ($request->boolean('auto')) {
            $reviewer = $this->workflow->autoAssign($submission);

            return back()->with($reviewer ? 'success' : 'error', $reviewer
                ? "Assigned to {$reviewer->name}."
                : 'No eligible reviewer covers this content category — the manuscript stays pending assignment.');
        }

        $data = $request->validate(['reviewer_id' => ['required', 'integer', 'exists:users,id']]);
        $reviewer = User::findOrFail($data['reviewer_id']);
        $this->workflow->assign($submission, $reviewer, $request->user());

        return back()->with('success', "Assigned to {$reviewer->name}.");
    }

    public function decide(Request $request, ManuscriptSubmission $submission): RedirectResponse
    {
        Gate::authorize('review', $submission);

        $data = $request->validate([
            'decision' => ['required', Rule::enum(RevisionDecision::class)],
            'reviewer_remarks' => ['nullable', 'required_unless:decision,approved', 'string', 'max:10000'],
        ]);

        $this->workflow->decide($submission, $request->user(), RevisionDecision::from($data['decision']), $data['reviewer_remarks'] ?? null);

        return back()->with('success', 'Decision recorded: '.RevisionDecision::from($data['decision'])->label().'.');
    }

    public function changeStage(Request $request, ManuscriptSubmission $submission): RedirectResponse
    {
        Gate::authorize('changeStage', $submission);

        $data = $request->validate([
            'stage' => ['required', Rule::enum(ManuscriptStage::class)],
            'remarks' => ['nullable', 'string', 'max:255'],
        ]);

        $this->workflow->changeStage($submission, ManuscriptStage::from($data['stage']), $request->user(), $data['remarks'] ?? null);

        return back()->with('success', 'Stage changed to '.ManuscriptStage::from($data['stage'])->label().'.');
    }

    public function recheck(ManuscriptSubmission $submission): RedirectResponse
    {
        abort_unless(auth()->user()->can('plagiarism-checks.recheck') && Gate::allows('view', $submission), 403);

        $this->workflow->startPlagiarismCheck($submission);
        activity()->log('Plagiarism Checks', 'Re-check requested', $submission);

        return back()->with('success', 'Plagiarism re-check queued.');
    }

    /** Kept for the manuscript page; Admin → Best Paper Awards is the main place to manage winners. */
    public function awardBestPaper(Request $request, ManuscriptSubmission $submission, SaveBestPaperAward $save): RedirectResponse
    {
        Gate::authorize('awardBestPaper', $submission);

        $data = $request->validate([
            'award_quarter' => ['required', Rule::in(BestPaperAward::QUARTERS)],
            'award_year' => ['required', 'integer', 'between:2000,2100'],
            'prize_amount' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'editorial_citation' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $award = $save->handle($data + ['manuscript_submission_id' => $submission->id], $request->user());
        } catch (ValidationException $e) {
            // The manuscript page shows award errors next to the quarter field.
            throw ValidationException::withMessages(['award_quarter' => collect($e->errors())->flatten()->first()]);
        }

        return back()->with('success', "Marked as Best Paper — {$award->periodLabel()}.");
    }

    public function removeAward(ManuscriptSubmission $submission, BestPaperAward $award, SaveBestPaperAward $save): RedirectResponse
    {
        Gate::authorize('awardBestPaper', $submission);
        abort_unless($award->manuscript_submission_id === $submission->id, 404);

        $save->delete($award);

        return back()->with('success', 'Award removed.');
    }
}
