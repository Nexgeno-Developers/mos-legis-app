<?php

namespace App\Services\Documents;

use App\Models\ManuscriptSubmission;
use App\Models\PublicationCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Publication certificate (clarification #1): unique number, manuscript and author
 * details, category, publication date, volume/theme, issue date and verification URL.
 * The details are frozen in snapshot_json so later edits never change an issued certificate.
 */
class CertificateGenerator
{
    public function __construct(private readonly DocumentRenderer $documents) {}

    public function issue(ManuscriptSubmission $submission): PublicationCertificate
    {
        if ($existing = $submission->certificate()->first()) {
            return $existing;
        }

        $submission->loadMissing(['author', 'contentCategory', 'theme']);

        $certificate = new PublicationCertificate([
            'manuscript_submission_id' => $submission->id,
            'certificate_number' => sprintf('MOS-CERT-%d-%05d', now()->year, $submission->id),
            'verification_slug' => Str::lower(Str::random(32)),
            'issued_at' => now(),
            'snapshot_json' => [
                'reference' => $submission->reference(),
                'title' => $submission->title,
                'author' => $submission->author->name,
                'co_authors' => array_values($submission->co_authors ?? []),
                'institution' => $submission->institution,
                'content_category' => $submission->contentCategory->name,
                'volume' => $submission->theme?->volume,
                'theme' => $submission->theme?->name,
                'theme_period' => $submission->theme?->periodLabel(),
                'published_at' => ($submission->published_at ?? now())->toDateString(),
            ],
        ]);
        $certificate->document_path = "certificates/{$certificate->certificate_number}.pdf";

        Storage::disk('local')->put($certificate->document_path, $this->render($certificate));
        $certificate->save();

        activity()->log('Submissions', 'Publication certificate issued', $submission, ['certificate_number' => $certificate->certificate_number]);

        return $certificate;
    }

    public function render(PublicationCertificate $certificate): string
    {
        return Pdf::loadView('pdf.certificate', ['certificate' => $certificate] + $this->documents->common())
            ->setPaper('a4', 'landscape')
            ->output();
    }
}
