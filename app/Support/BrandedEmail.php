<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Wraps any email body in the branded MOS Legis frame (resources/views/emails/layout.blade.php),
 * so every email — notification templates edited in the admin and system emails like the OTP —
 * goes out in the same standard format.
 */
final class BrandedEmail
{
    /**
     * @param  array{preheader?: string|null, action?: array{label: string, url: string}|null}  $options
     */
    public static function render(string $subject, string $bodyHtml, array $options = []): string
    {
        return view('emails.layout', [
            'subject' => $subject,
            'body' => $bodyHtml,
            'preheader' => $options['preheader'] ?? Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(preg_replace('/<[^>]+>/', ' $0', $bodyHtml))))), 110),
            'action' => $options['action'] ?? null,
            'brand' => self::brand(),
        ])->render();
    }

    /** @return array<string, mixed> */
    private static function brand(): array
    {
        $logo = settings('general.application_logo');
        $url = rtrim((string) config('app.url'), '/');

        return [
            'name' => settings('general.application_name') ?: 'MOS Legis',
            'logo' => $logo ? Storage::disk('public')->url($logo) : asset('images/logo-mark.png'),
            'url' => $url ?: url('/'),
            'domain' => parse_url($url, PHP_URL_HOST) ?: $url,
            'email' => settings('general.application_email'),
            'phone' => settings('general.contact_number'),
            'address' => settings('general.address'),
            'socials' => array_filter([
                'LinkedIn' => settings('seo_social.linkedin_url'),
                'Instagram' => settings('seo_social.instagram_url'),
                'Facebook' => settings('seo_social.facebook_url'),
                'YouTube' => settings('seo_social.youtube_url'),
                'X' => settings('seo_social.x_url'),
            ]),
        ];
    }
}
