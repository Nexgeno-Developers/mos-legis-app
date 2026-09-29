<?php

namespace App\Http\Controllers\Account;

use App\Enums\PlagiarismCheckType;
use App\Http\Controllers\Controller;
use App\Models\PlagiarismCheck;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * SOW C.10 — the author's standalone plagiarism checks and their reports.
 */
class PlagiarismCheckController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.plagiarism-checks.index', [
            'checks' => PlagiarismCheck::where('user_id', $request->user()->id)->where('check_type', PlagiarismCheckType::Standalone)
                ->with('payment')->latest('id')->paginate(10),
        ]);
    }

    public function show(Request $request, PlagiarismCheck $check): View
    {
        abort_unless($check->user_id === $request->user()->id, 403);

        return view('account.plagiarism-checks.show', [
            'check' => $check->load('payment'),
            'threshold' => settings()->float('manuscript.plagiarism_max_similarity_percent'),
        ]);
    }

    public function report(Request $request, PlagiarismCheck $check): StreamedResponse
    {
        abort_unless($check->user_id === $request->user()->id && $check->report_file && Storage::disk('local')->exists($check->report_file), 404);

        return Storage::disk('local')->download($check->report_file, "plagiarism-report-{$check->id}.pdf");
    }
}
