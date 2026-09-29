<?php

namespace App\Http\Controllers\Site;

use App\Enums\PlagiarismCheckStatus;
use App\Enums\PlagiarismCheckType;
use App\Http\Controllers\Controller;
use App\Jobs\RunPlagiarismCheck;
use App\Models\PlagiarismCheck;
use App\Services\Manuscripts\DocxWordCounter;
use App\Services\Manuscripts\FeeCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/**
 * SOW C.10 — paid standalone plagiarism check (paste text or upload .docx).
 */
class PlagiarismCheckerController extends Controller
{
    public function show(FeeCalculator $fees): View
    {
        return view('site.plagiarism-checker', [
            'fee' => $fees->standaloneCheckFee(),
            'threshold' => settings()->float('manuscript.plagiarism_max_similarity_percent'),
        ]);
    }

    public function store(Request $request, DocxWordCounter $docx, FeeCalculator $fees): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'required_without:document', 'string', 'min:50', 'max:200000'],
            'document' => ['nullable', 'required_without:content', 'file', 'mimes:docx', 'max:20480'],
        ]);

        $path = null;
        if ($request->hasFile('document')) {
            try {
                $docx->text($request->file('document')->getRealPath());
            } catch (Throwable) {
                throw ValidationException::withMessages(['document' => 'The file could not be read. Upload a valid .docx document.']);
            }
            $path = $request->file('document')->store("plagiarism-checks/{$request->user()->id}", 'local');
        }

        $check = PlagiarismCheck::create([
            'user_id' => $request->user()->id,
            'check_type' => PlagiarismCheckType::Standalone,
            'title' => $data['title'],
            'content' => $path ? null : $data['content'],
            'uploaded_file' => $path,
            'check_status' => PlagiarismCheckStatus::Pending,
        ]);

        activity()->log('Plagiarism Checks', 'Standalone check requested', $check);

        if (! $fees->paymentsEnabled()) {
            RunPlagiarismCheck::dispatch($check);

            return redirect()->route('account.plagiarism-checks.show', $check);
        }

        return redirect()->route('account.checkout.plagiarism', $check);
    }
}
