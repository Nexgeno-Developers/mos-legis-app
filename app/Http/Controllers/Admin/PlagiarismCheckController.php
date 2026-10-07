<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlagiarismCheckStatus;
use App\Enums\PlagiarismCheckType;
use App\Http\Controllers\Controller;
use App\Jobs\RunPlagiarismCheck;
use App\Models\PlagiarismCheck;
use App\Services\Plagiarism\OriginalityPlagiarismChecker;
use App\Services\Plagiarism\PlagiarismChecker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * SOW A.18 — Plagiarism Checks Management (manuscript and standalone checks, history, re-check).
 */
class PlagiarismCheckController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:plagiarism-checks.view'),
            new Middleware('can:plagiarism-checks.recheck', only: ['recheck']),
        ];
    }

    public function index(Request $request): View
    {
        $checks = PlagiarismCheck::query()
            ->with(['user:id,name', 'payment:id,payment_status', 'submission:id'])
            ->when($request->string('search')->trim()->value(), function ($q, $search) {
                $id = (int) preg_replace('/\D/', '', $search);
                $q->where(fn ($q) => $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                    ->when($id, fn ($q) => $q->orWhere('id', $id)->orWhere('manuscript_submission_id', $id)));
            })
            ->when($request->enum('check_type', PlagiarismCheckType::class), fn ($q, $type) => $q->where('check_type', $type))
            ->when($request->enum('check_status', PlagiarismCheckStatus::class), fn ($q, $status) => $q->where('check_status', $status))
            ->when($request->string('payment_status')->value(), fn ($q, $status) => $status === 'unpaid'
                ? $q->whereNull('payment_id')
                : $q->whereHas('payment', fn ($q) => $q->where('payment_status', $status)))
            ->when($request->date('from'), fn ($q, $from) => $q->where('created_at', '>=', $from->startOfDay()))
            ->when($request->date('to'), fn ($q, $to) => $q->where('created_at', '<=', $to->endOfDay()))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $provider = config('services.plagiarism.driver');

        return view('admin.plagiarism-checks.index', [
            'checks' => $checks,
            'provider' => $provider,
            'balance' => $provider === 'originality' ? $this->originalityBalance() : null,
            'threshold' => settings()->float('manuscript.plagiarism_max_similarity_percent'),
        ]);
    }

    public function show(PlagiarismCheck $plagiarismCheck): View
    {
        $plagiarismCheck->load(['user', 'submission', 'payment']);

        return view('admin.plagiarism-checks.show', [
            'check' => $plagiarismCheck,
            'history' => PlagiarismCheck::query()
                ->when($plagiarismCheck->manuscript_submission_id,
                    fn ($q, $id) => $q->where('manuscript_submission_id', $id),
                    fn ($q) => $q->whereKey($plagiarismCheck->id))
                ->latest('id')->get(),
            'threshold' => settings()->float('manuscript.plagiarism_max_similarity_percent'),
        ]);
    }

    /** Originality.ai credits (cached 10 minutes); null when the API can't be reached or the key is wrong. */
    private function originalityBalance(): ?array
    {
        $checker = app(PlagiarismChecker::class);

        if (! $checker instanceof OriginalityPlagiarismChecker) {
            return null;
        }

        return Cache::remember('originality-balance', now()->addMinutes(10), fn () => rescue(fn () => $checker->balance(), null, false));
    }

    /** Re-runs the check as a new history row; manuscript stage rules apply only while still in screening. */
    public function recheck(PlagiarismCheck $plagiarismCheck): RedirectResponse
    {
        if (! $plagiarismCheck->canBeRechecked()) {
            return back()->with('error', 'This standalone check has not been paid for, so it cannot be re-run.');
        }

        $copy = $plagiarismCheck->replicate(['similarity_percentage', 'api_response', 'report_file', 'checked_at', 'check_status']);
        $copy->check_status = PlagiarismCheckStatus::Pending;
        $copy->save();

        RunPlagiarismCheck::dispatch($copy)->afterCommit();
        activity()->log('Plagiarism Checks', 'Re-check requested', $copy, ['source_check_id' => $plagiarismCheck->id]);

        return redirect()->route('admin.plagiarism-checks.show', $copy)->with('success', 'Re-check queued.');
    }

    public function report(PlagiarismCheck $plagiarismCheck): StreamedResponse
    {
        abort_unless($plagiarismCheck->report_file && Storage::disk('local')->exists($plagiarismCheck->report_file), 404);

        return Storage::disk('local')->download($plagiarismCheck->report_file, "plagiarism-report-{$plagiarismCheck->id}.pdf");
    }

    public function file(PlagiarismCheck $plagiarismCheck): StreamedResponse
    {
        abort_unless($plagiarismCheck->uploaded_file && Storage::disk('local')->exists($plagiarismCheck->uploaded_file), 404);

        return Storage::disk('local')->download($plagiarismCheck->uploaded_file, "plagiarism-check-{$plagiarismCheck->id}.docx");
    }
}
