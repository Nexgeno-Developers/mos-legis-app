<?php

namespace App\Http\Controllers\Site;

use App\Enums\EmploymentType;
use App\Enums\WorkMode;
use App\Http\Controllers\Controller;
use App\Models\JobPosting;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SOW C.03 — live job listings with filters and a detail modal; Apply Now uses the source URL.
 */
class JobController extends Controller
{
    public function __invoke(Request $request): View
    {
        $jobs = JobPosting::live()
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('job_title', 'like', "%{$search}%")
                ->orWhere('organisation', 'like', "%{$search}%")
                ->orWhere('required_skills', 'like', "%{$search}%")))
            ->when($request->string('location')->trim()->value(), fn ($q, $location) => $q->where('location', 'like', "%{$location}%"))
            ->when($request->string('practice_area')->value(), fn ($q, $area) => $q->where('practice_area', $area))
            ->when($request->enum('work_mode', WorkMode::class), fn ($q, $mode) => $q->where('work_mode', $mode))
            ->when($request->enum('employment_type', EmploymentType::class), fn ($q, $type) => $q->where('employment_type', $type))
            ->when($request->string('experience')->trim()->value(), fn ($q, $exp) => $q->where('experience', 'like', "%{$exp}%"))
            ->when($request->date('deadline_after'), fn ($q, $date) => $q->whereDate('application_deadline', '>=', $date))
            ->when($request->date('posted_after'), fn ($q, $date) => $q->whereDate('published_date', '>=', $date))
            ->latest('published_date')
            ->paginate(12)
            ->withQueryString();

        return view('site.jobs', [
            'jobs' => $jobs,
            'practiceAreas' => JobPosting::live()->distinct()->orderBy('practice_area')->pluck('practice_area', 'practice_area'),
        ]);
    }
}
