<?php

namespace App\Http\Controllers\Account;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobPostingRequest;
use App\Models\JobPosting;
use App\Notifications\WorkflowNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * SOW B.06 — authors post and manage their own job listings. With "Job posting approval required"
 * on, a new job is listed only after an admin approves it (admins are notified).
 */
class JobPostingController extends Controller
{
    public function __construct(private readonly WorkflowNotifier $notifier) {}

    public function index(Request $request): View
    {
        return view('account.jobs.index', ['jobs' => $request->user()->jobPostings()->latest('id')->paginate(10)]);
    }

    public function create(): View
    {
        return view('account.jobs.form', ['job' => new JobPosting([
            'published_date' => today(), 'expiry_date' => today()->addDays(30), 'application_deadline' => today()->addDays(21), 'status' => RecordStatus::Active,
        ])]);
    }

    public function store(JobPostingRequest $request): RedirectResponse
    {
        $needsApproval = settings()->bool('approvals.job_author_approval_required');
        $job = $request->user()->jobPostings()->create($request->jobData() + ['approved_at' => $needsApproval ? null : now()]);
        activity()->log('Job Postings', 'Author posted job', $job, ['title' => $job->job_title, 'needs_approval' => $needsApproval]);

        if ($needsApproval) {
            $this->notifier->toAdmins('job_pending_approval', ['title' => $job->job_title, 'author_name' => $request->user()->name]);

            return redirect()->route('account.jobs.index')->with('success', 'Job submitted. It will be listed once an admin approves it.');
        }

        return redirect()->route('account.jobs.index')->with('success', 'Job posted.');
    }

    public function edit(JobPosting $job): View
    {
        Gate::authorize('update', $job);

        return view('account.jobs.form', ['job' => $job]);
    }

    public function update(JobPostingRequest $request, JobPosting $job): RedirectResponse
    {
        $job->update($request->jobData());

        return redirect()->route('account.jobs.index')->with('success', 'Job updated.');
    }

    public function destroy(JobPosting $job): RedirectResponse
    {
        Gate::authorize('delete', $job);
        $job->delete();

        return back()->with('success', 'Job deleted.');
    }
}
