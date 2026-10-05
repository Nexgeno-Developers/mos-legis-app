<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EmploymentType;
use App\Enums\RecordStatus;
use App\Enums\WorkMode;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobPostingRequest;
use App\Models\JobPosting;
use App\Notifications\WorkflowNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * SOW A.04 — Jobs Posting Management.
 */
class JobPostingController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', JobPosting::class);

        $jobs = JobPosting::query()
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('job_title', 'like', "%{$search}%")
                ->orWhere('organisation', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhere('required_skills', 'like', "%{$search}%")))
            ->when($request->string('practice_area')->value(), fn ($q, $area) => $q->where('practice_area', $area))
            ->when($request->enum('work_mode', WorkMode::class), fn ($q, $mode) => $q->where('work_mode', $mode))
            ->when($request->enum('employment_type', EmploymentType::class), fn ($q, $type) => $q->where('employment_type', $type))
            ->when($request->string('experience')->trim()->value(), fn ($q, $exp) => $q->where('experience', 'like', "%{$exp}%"))
            ->when($request->string('status')->value(), fn ($q, $status) => match ($status) {
                'expired' => $q->expired(),
                'pending' => $q->pendingApproval(),
                default => $q->where('status', $status),
            })
            ->when($request->date('published_from'), fn ($q, $d) => $q->whereDate('published_date', '>=', $d))
            ->when($request->date('published_to'), fn ($q, $d) => $q->whereDate('published_date', '<=', $d))
            ->when($request->date('deadline_from'), fn ($q, $d) => $q->whereDate('application_deadline', '>=', $d))
            ->when($request->date('deadline_to'), fn ($q, $d) => $q->whereDate('application_deadline', '<=', $d))
            ->when($request->date('expiry_from'), fn ($q, $d) => $q->whereDate('expiry_date', '>=', $d))
            ->when($request->date('expiry_to'), fn ($q, $d) => $q->whereDate('expiry_date', '<=', $d))
            ->latest('published_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.job-postings.index', [
            'jobs' => $jobs,
            'practiceAreas' => JobPosting::query()->distinct()->orderBy('practice_area')->pluck('practice_area', 'practice_area'),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', JobPosting::class);

        return view('admin.job-postings.form', ['job' => new JobPosting([
            'published_date' => today(), 'expiry_date' => today()->addDays(30), 'application_deadline' => today()->addDays(21),
            'status' => RecordStatus::Active,
        ])]);
    }

    public function store(JobPostingRequest $request): RedirectResponse
    {
        $job = JobPosting::create($request->jobData() + ['user_id' => $request->user()->id, 'approved_at' => now()]);
        activity()->log('Job Postings', 'Created job posting', $job, $request->safe()->only(['job_title', 'organisation', 'status']));

        return redirect()->route('admin.job-postings.index')->with('success', 'Job posting created.');
    }

    public function show(JobPosting $jobPosting): View
    {
        Gate::authorize('view', $jobPosting);

        return view('admin.job-postings.show', ['job' => $jobPosting->load('user:id,name')]);
    }

    public function edit(JobPosting $jobPosting): View
    {
        Gate::authorize('update', $jobPosting);

        return view('admin.job-postings.form', ['job' => $jobPosting]);
    }

    public function update(JobPostingRequest $request, JobPosting $jobPosting): RedirectResponse
    {
        $jobPosting->update($request->jobData());
        activity()->log('Job Postings', 'Updated job posting', $jobPosting, $request->safe()->only(['job_title', 'organisation', 'status']));

        return redirect()->route('admin.job-postings.index')->with('success', 'Job posting updated.');
    }

    /** Approves an author's job posting so it is listed; the author is told. */
    public function approve(JobPosting $jobPosting, WorkflowNotifier $notifier): RedirectResponse
    {
        Gate::authorize('update', $jobPosting);

        if ($jobPosting->isPendingApproval()) {
            $jobPosting->forceFill(['approved_at' => now()])->save();
            activity()->log('Job Postings', 'Approved job posting', $jobPosting);

            if ($jobPosting->user?->isAuthor()) {
                $notifier->toUser('job_approved', $jobPosting->user, ['title' => $jobPosting->job_title]);
            }
        }

        return back()->with('success', "{$jobPosting->job_title} is approved.");
    }

    public function toggleStatus(JobPosting $jobPosting): RedirectResponse
    {
        Gate::authorize('update', $jobPosting);

        $jobPosting->update(['status' => $jobPosting->isActive() ? RecordStatus::Inactive : RecordStatus::Active]);
        activity()->log('Job Postings', 'Changed status to '.$jobPosting->status->value, $jobPosting);

        return back()->with('success', "{$jobPosting->job_title} is now {$jobPosting->status->value}.");
    }

    public function destroy(JobPosting $jobPosting): RedirectResponse
    {
        Gate::authorize('delete', $jobPosting);

        activity()->log('Job Postings', 'Deleted job posting', $jobPosting, ['title' => $jobPosting->job_title]);
        $jobPosting->delete();

        return redirect()->route('admin.job-postings.index')->with('success', 'Job posting deleted.');
    }
}
