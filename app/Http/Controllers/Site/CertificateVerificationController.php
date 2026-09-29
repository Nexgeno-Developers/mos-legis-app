<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\PublicationCertificate;
use Illuminate\View\View;

/**
 * Public certificate verification URL printed on every certificate (clarification #1).
 */
class CertificateVerificationController extends Controller
{
    public function __invoke(string $slug): View
    {
        $certificate = PublicationCertificate::with('submission:id,stage,title')
            ->where('verification_slug', $slug)
            ->first();

        return view('site.certificate-verify', ['certificate' => $certificate, 'slug' => $slug]);
    }
}
