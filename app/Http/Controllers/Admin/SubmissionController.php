<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Manuscripts\SaveSubmission;
use App\Enums\ManuscriptStage;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManuscriptSubmissionRequest;
use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ManuscriptRevision;
use App\Models\ManuscriptSubmission;
use App\Models\User;
use App\Services\Manuscripts\FeeCalculator;
use App\Services\Manuscripts\ReviewerAllocator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * SOW A.16 — Manuscript Submissions Management.
 */
class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', ManuscriptSubmission::class);
        $user = $request->user();

        $submissions = ManuscriptSubmission::query()
            ->visibleTo($user)
            ->with(['author:id,name', 'reviewer:id,name', 'contentCategory:id,name'])
            ->when($request->string('search')->trim()->value(), function ($q, $search) {
                $id = (int) preg_replace('/\D/', '', $search);
                $q->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('author', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->when($id, fn ($q) => $q->orWhere('id', $id)));
            })
            ->when($request->enum('stage', ManuscriptStage::class), fn ($q, $stage) => $q->where('stage', $stage))
            ->when($request->string('reviewer')->value(), fn ($q, $reviewer) => $reviewer === 'unassigned'
                ? $q->whereNull('assigned_to')
                : $q->where('assigned_to', (int) $reviewer))
            ->when($request->integer('content_category_id'), fn ($q, $id) => $q->where('content_category_id', $id))
            ->when($request->date('from'), fn ($q, $from) => $q->where('created_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($q, $to) => $q->where('created_at', '<=', $to->endOfDay()))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.submissions.index', [
            'submissions' => $submissions,
            'reviewers' => ['unassigned' => 'Unassigned'] + User::role(RoleName::Reviewer->value)->orderBy('name')->pluck('name', 'id')->all(),
            'contentCategories' => ContentCategory::orderBy('name')->pluck('name', 'id'),
            'threshold' => settings()->float('manuscript.plagiarism_max_similarity_percent'),
        ]);
    }

    public function show(ManuscriptSubmission $submission, ReviewerAllocator $allocator, FeeCalculator $fees): View
    {
        Gate::authorize('view', $submission);

        $submission->load([
            'author.authorProfile', 'reviewer:id,name,email', 'authorCategory', 'contentCategory', 'theme',
            'revisions.reviewer:id,name', 'payments', 'plagiarismChecks', 'certificate', 'awards.selector:id,name',
        ]);

        return view('admin.submissions.show', [
            'submission' => $submission,
            'eligibleReviewers' => $allocator->eligible($submission->content_category_id),
            'publicationFee' => $fees->publicationFeeFor($submission),
            'threshold' => settings()->float('manuscript.plagiarism_max_similarity_percent'),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', ManuscriptSubmission::class);

        return view('admin.submissions.form', ['submission' => new ManuscriptSubmission] + $this->formOptions());
    }

    public function store(ManuscriptSubmissionRequest $request, SaveSubmission $saveSubmission): RedirectResponse
    {
        $author = User::findOrFail($request->validated('user_id'));
        abort_unless($author->isAuthor(), 422, 'Manuscripts can only be submitted for author accounts.');

        $submission = $saveSubmission->create($request, $author);

        return redirect()->route('admin.submissions.show', $submission)
            ->with('success', "Manuscript {$submission->reference()} created. The author can pay the pre-screening fee from their account.");
    }

    public function edit(ManuscriptSubmission $submission): View
    {
        Gate::authorize('update', $submission);

        return view('admin.submissions.form', ['submission' => $submission] + $this->formOptions());
    }

    public function update(ManuscriptSubmissionRequest $request, ManuscriptSubmission $submission, SaveSubmission $saveSubmission): RedirectResponse
    {
        $saveSubmission->update($request, $submission);
        activity()->log('Submissions', 'Manuscript details edited', $submission, $request->safe()->except(['manuscript']));

        return redirect()->route('admin.submissions.show', $submission)->with('success', 'Manuscript updated.');
    }

    public function destroy(ManuscriptSubmission $submission): RedirectResponse
    {
        Gate::authorize('delete', $submission);

        activity()->log('Submissions', 'Manuscript deleted', $submission, ['title' => $submission->title]);
        $files = collect([$submission->manuscript_attachment])
            ->merge($submission->revisions()->pluck('resubmitted_attachment'))
            ->merge($submission->plagiarismChecks()->pluck('report_file'))
            ->filter()->all();

        $submission->payments()->delete();
        $submission->delete();
        Storage::disk('local')->delete($files);

        return redirect()->route('admin.submissions.index')->with('success', 'Manuscript deleted.');
    }

    /** Current manuscript file or a revision's file. */
    public function download(ManuscriptSubmission $submission, ?ManuscriptRevision $revision = null, string $version = 'current'): StreamedResponse
    {
        Gate::authorize('view', $submission);

        $path = match (true) {
            $revision && $version === 'reviewed' => $revision->reviewed_attachment,
            $revision !== null => $revision->resubmitted_attachment,
            default => $submission->manuscript_attachment,
        };
        abort_unless($path && Storage::disk('local')->exists($path), 404);
        abort_if($revision && $revision->manuscript_submission_id !== $submission->id, 404);

        return Storage::disk('local')->download($path, $submission->reference().'.docx');
    }

    public function certificate(ManuscriptSubmission $submission): StreamedResponse
    {
        Gate::authorize('view', $submission);
        $certificate = $submission->certificate;
        abort_unless($certificate && Storage::disk('local')->exists($certificate->document_path), 404);

        return Storage::disk('local')->download($certificate->document_path, $certificate->certificate_number.'.pdf');
    }

    private function formOptions(): array
    {
        $authors = User::role(RoleName::Author->value)->with(['authorProfile.authorCategory:id,name', 'address'])->orderBy('name')->get(['id', 'name', 'email']);
        $countries = config('countries');

        return [
            'authors' => $authors->mapWithKeys(fn (User $u) => [$u->id => "{$u->name} ({$u->email})"]),
            // Profile details shown when an author is picked; the submission records them on save.
            'authorDetails' => $authors->mapWithKeys(fn (User $u) => [$u->id => [
                'category' => $u->authorProfile?->authorCategory?->name,
                'institution' => $u->authorProfile?->institution,
                'country' => $u->authorProfile?->country ?: ($u->address ? ($countries[$u->address->country_code] ?? null) : null),
                'editUrl' => route('admin.users.edit', $u),
            ]]),
            'authorCategories' => AuthorCategory::active()->orderBy('name')->pluck('name', 'id'),
            'contentCategories' => ContentCategory::active()->orderBy('name')->get(),
        ];
    }
}
