<?php

namespace App\Services\Documents;

use App\Models\Payment;
use App\Models\PlagiarismCheck;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * PDF documents: invoices (rendered on demand from the frozen payment data)
 * and plagiarism reports (stored when a check completes).
 */
class DocumentRenderer
{
    public function invoice(Payment $payment): string
    {
        $payment->loadMissing(['user', 'payable']);

        return Pdf::loadView('pdf.invoice', ['payment' => $payment] + $this->common())->output();
    }

    public function storePlagiarismReport(PlagiarismCheck $check): string
    {
        $path = "plagiarism-reports/check-{$check->id}.pdf";
        Storage::disk('local')->put($path, Pdf::loadView('pdf.plagiarism-report', ['check' => $check->loadMissing('user', 'submission')] + $this->common())->output());

        return $path;
    }

    /** @return array<string, mixed> */
    public function common(): array
    {
        $logo = public_path('images/logo.png');

        return [
            // dompdf needs the GD extension to embed PNG images.
            'logo' => extension_loaded('gd') && is_file($logo) ? 'data:image/png;base64,'.base64_encode(file_get_contents($logo)) : null,
            'appName' => settings('general.application_name'),
            'appEmail' => settings('general.application_email'),
            'appAddress' => settings('general.address'),
            'appPhone' => settings('general.contact_number'),
        ];
    }
}
