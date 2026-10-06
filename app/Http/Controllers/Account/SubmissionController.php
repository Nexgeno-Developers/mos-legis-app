<?php

namespace App\Http\Controllers\Account;

use App\Actions\Manuscripts\SaveSubmission;
use App\Enums\ManuscriptStage;
use App\Enums\PaymentPurpose;
use App\Http\Controllers\Controller;
use App\Http\Requests\ManuscriptSubmissionRequest;
use App\Models\ManuscriptSubmission;
use App\Services\Documents\CertificateGenerator;
use App\Services\Manuscripts\DocxWordCounter;
use App\Services\Manuscripts\FeeCalculator;
use App\Services\Manuscripts\ManuscriptWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 * SOW B.04 — My Submissions: list, new submission (step form on /submit), detail, revision upload.
 */
class SubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $submissions = $request->user()->submissions()
            ->with(['contentCategory:id,name', 'prescreeningPayment', 'publicationPayment'])
            ->when($request->string('search')->trim()->value(), function ($q, $search) {
                $id = (int) preg_replace('/\D/', '', $search);
                $q->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->when($id, fn ($q) => $q->orWhere('id', $id)));
            })
            ->when($request->enum('stage', ManuscriptStage::class), fn ($q, $stage) => $q->where('stage', $stage))
            ->when($request->date('from'), fn ($q, $from) => $q->where('created_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($q, $to) => $q->where('created_at', '<=', $to->endOfDay()))
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('account.submissions.index', compact('submissions'));
    }

    public function store(ManuscriptSubmissionRequest $request, SaveSubmission $saveSubmission, FeeCalculator $fees): RedirectResponse
    {
        $submission = $saveSubmission->create($request, $request->user());

        if (! $fees->paymentsEnabled()) {
            return redirect()->route('account.submissions.show', $submission)->with('success', "Manuscript {$submission->reference()} submitted.");
        }

        return redirect()->route('account.checkout.submission', [$submission, PaymentPurpose::Prescreening->value])
            ->with('success', "Manuscript {$submission->reference()} submitted. Pay the plagiarism pre-screening fee to start screening.");
    }

    public function show(Request $request, ManuscriptSubmission $submission, FeeCalculator $fees): View
    {
        Gate::authorize('view', $submission);

        $submission->load(['contentCategory', 'authorCategory', 'theme', 'revisions', 'payments', 'plagiarismChecks', 'certificate', 'awards']);

        return view('account.submissions.show', [
            'submission' => $submission,
            'publicationFee' => $fees->publicationFeeFor($submission),
            'publicationBreakdown' => $fees->publicationBreakdown($submission),
            'prescreeningFee' => $fees->prescreeningFee(),
            'paymentsEnabled' => $fees->paymentsEnabled(),
        ]);
    }

    public function resubmit(Request $request, ManuscriptSubmission $submission, DocxWordCounter $counter, ManuscriptWorkflow $workflow): RedirectResponse
    {
        Gate::authorize('resubmit', $submission);

        $data = $request->validate([
            'manuscript' => ['required', 'file', 'mimes:docx', 'max:20480'],
            'author_response' => ['nullable', 'string', 'max:5000'],
        ]);

        try {
            $words = $counter->count($request->file('manuscript')->getRealPath());
        } catch (Throwable) {
            throw ValidationException::withMessages(['manuscript' => 'The file could not be read. Upload a valid .docx document.']);
        }

        $category = $submission->contentCategory;
        if ($words < $category->min_word_limit || $words > $category->max_word_limit) {
            throw ValidationException::withMessages(['manuscript' => "The revised manuscript has {$words} words; {$category->name} accepts {$category->wordLimitLabel()}."]);
        }

        $path = $request->file('manuscript')->store("manuscripts/{$submission->user_id}", 'local');
        $workflow->resubmit($submission, $path, $words, $data['author_response'] ?? null);

        return back()->with('success', 'Revised manuscript submitted for review.');
    }

    public function download(ManuscriptSubmission $submission): StreamedResponse
    {
        Gate::authorize('view', $submission);
        abort_unless(Storage::disk('local')->exists($submission->manuscript_attachment), 404);

        return Storage::disk('local')->download($submission->manuscript_attachment, $submission->reference().'.docx');
    }

    /** Rendered with the current certificate design from the frozen details. */
    public function certificate(ManuscriptSubmission $submission, CertificateGenerator $certificates): Response
    {
        Gate::authorize('view', $submission);
        $certificate = $submission->certificate;
        abort_unless($certificate, 404);

        return $certificates->download($certificate);
    }
}
