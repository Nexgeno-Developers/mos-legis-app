<?php

namespace App\Services\Documents;

use App\Models\ManuscriptSubmission;
use App\Models\PublicationCertificate;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Publication certificate (clarification #1): unique number, manuscript and author details, category,
 * publication date, volume, signatory, ISSN and a QR code linking to the verification page.
 * The details are frozen in snapshot_json so later edits never change an issued certificate; the design
 * is re-applied whenever the certificate is downloaded.
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
            'certificate_number' => sprintf('MLR/COP/%d/%06d', now()->year, $submission->id),
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
        $certificate->document_path = 'certificates/'.self::fileName($certificate);

        Storage::disk('local')->put($certificate->document_path, $this->render($certificate));
        $certificate->save();

        activity()->log('Submissions', 'Publication certificate issued', $submission, ['certificate_number' => $certificate->certificate_number]);

        return $certificate;
    }

    /** Download response with the current design (the stored copy is refreshed too). */
    public function download(PublicationCertificate $certificate): Response
    {
        $pdf = $this->render($certificate);
        Storage::disk('local')->put($certificate->document_path, $pdf);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.self::fileName($certificate).'"',
        ]);
    }

    public function render(PublicationCertificate $certificate): string
    {
        return Pdf::loadView('pdf.certificate', [
            'certificate' => $certificate,
            'qr' => $this->qrCode($certificate->verificationUrl()),
            'signature' => $this->publicImage(settings('manuscript.certificate_signature')),
            'signatoryName' => settings('manuscript.certificate_signatory_name'),
            'signatoryTitle' => settings('manuscript.certificate_signatory_title'),
            'issn' => settings('manuscript.journal_issn'),
            'website' => preg_replace('#^https?://#', '', rtrim(url('/'), '/')),
        ] + $this->documents->common())
            ->setPaper('a4', 'landscape')
            ->output();
    }

    /** "MLR/COP/2026/000001" → "MLR-COP-2026-000001.pdf". */
    public static function fileName(PublicationCertificate $certificate): string
    {
        return str_replace(['/', '\\'], '-', $certificate->certificate_number).'.pdf';
    }

    /** QR code for the verification URL, as an SVG image (no GD needed). */
    private function qrCode(string $url): string
    {
        return (new QRCode(new QROptions([
            'outputType' => QROutputInterface::MARKUP_SVG,
            'outputBase64' => true,
            'svgAddXmlHeader' => false,
            'addQuietzone' => false,
        ])))->render($url);
    }

    /** An uploaded image (public disk) as a data URI; PNG needs the GD extension in dompdf. */
    private function publicImage(?string $path): ?string
    {
        if (blank($path) || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        $mime = Storage::disk('public')->mimeType($path);
        // dompdf reads JPEG and (with GD) PNG; WebP is not supported.
        if (($mime === 'image/png' && ! extension_loaded('gd')) || ! in_array($mime, ['image/png', 'image/jpeg'], true)) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($path));
    }
}
