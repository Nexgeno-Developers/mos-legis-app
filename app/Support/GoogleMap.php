<?php

namespace App\Support;

/**
 * Builds a safe Google Maps embed URL for the Contact page. Only
 * https://www.google.com/maps/embed… (or …/maps?…output=embed) URLs are allowed,
 * so admin input can never embed an arbitrary site in an iframe.
 */
final class GoogleMap
{
    /** Normalise admin input (embed URL or full <iframe> snippet) to an embed URL, or null if not Google Maps. */
    public static function embedUrl(?string $input): ?string
    {
        if (blank($input)) {
            return null;
        }

        $input = trim(html_entity_decode($input));

        if (preg_match('/src=["\']([^"\']+)["\']/i', $input, $match)) {
            $input = $match[1];
        }

        $parts = parse_url($input);
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';
        $query = $parts['query'] ?? '';

        $isGoogle = ($parts['scheme'] ?? '') === 'https' && in_array($host, ['www.google.com', 'google.com', 'maps.google.com'], true);
        $isEmbed = str_starts_with($path, '/maps/embed') || ($path === '/maps' && str_contains($query, 'output=embed'));

        return $isGoogle && $isEmbed ? $input : null;
    }

    /** Embed URL for a free-text address (no API key needed). */
    public static function forAddress(?string $address): ?string
    {
        $address = trim(preg_replace('/\s+/', ' ', (string) $address));

        return $address === '' ? null : 'https://www.google.com/maps?q='.rawurlencode($address).'&output=embed';
    }

    /** Link that opens the location in Google Maps (for "Get directions"). */
    public static function directionsUrl(?string $address): ?string
    {
        $address = trim(preg_replace('/\s+/', ' ', (string) $address));

        return $address === '' ? null : 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address);
    }
}
