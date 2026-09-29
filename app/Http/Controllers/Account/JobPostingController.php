<?php

namespace App\Http\Controllers\Account;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobPostingRequest;
use App\Models\JobPosting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * SOW B.06 — authors post and manage their own job listings.
 */
class JobPostingController extends Controller
{
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
        $job = $request->user()->jobPostings()->create($request->validated());
        activity()->log('Job Postings', 'Author posted job', $job, ['title' => $job->job_title]);

        return redirect()->route('account.jobs.index')->with('success', 'Job posted.');
    }

    public function edit(JobPosting $job): View
    {
        Gate::authorize('update', $job);

        return view('account.jobs.form', ['job' => $job]);
    }

    public function update(JobPostingRequest $request, JobPosting $job): RedirectResponse
    {
        $job->update($request->validated());

        return redirect()->route('account.jobs.index')->with('success', 'Job updated.');
    }

    public function destroy(JobPosting $job): RedirectResponse
    {
        Gate::authorize('delete', $job);
        $job->delete();

        return back()->with('success', 'Job deleted.');
    }
}
