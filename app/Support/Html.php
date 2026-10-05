<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitizes rich text before it is stored, so it can be rendered unescaped on the public site safely.
 * - clean(): author content (Trix output) — safe elements only, no inline styles.
 * - cleanRich(): admin content (the full editor) — also keeps formatting such as alignment, colours,
 *   font sizes, tables and images. Scripts, event handlers and javascript: links are always removed.
 */
final class Html
{
    private static ?HtmlSanitizer $sanitizer = null;

    private static ?HtmlSanitizer $richSanitizer = null;

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        return self::sanitizer()->sanitize($html);
    }

    public static function cleanRich(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        return self::richSanitizer()->sanitize($html);
    }

    private static function sanitizer(): HtmlSanitizer
    {
        return self::$sanitizer ??= new HtmlSanitizer(self::baseConfig());
    }

    private static function richSanitizer(): HtmlSanitizer
    {
        return self::$richSanitizer ??= new HtmlSanitizer(
            self::baseConfig()
                // Formatting from the editor: alignment, colours, sizes, table and image layout.
                ->allowAttribute('style', '*')
                ->allowAttribute('class', '*')
                ->allowElement('img', ['src', 'alt', 'title', 'width', 'height', 'style', 'class'])
                ->allowElement('table', ['style', 'class', 'border', 'cellpadding', 'cellspacing', 'width'])
                ->allowElement('td', ['style', 'class', 'colspan', 'rowspan', 'width'])
                ->allowElement('th', ['style', 'class', 'colspan', 'rowspan', 'width', 'scope'])
                ->allowElement('colgroup', ['style'])
                ->allowElement('col', ['style', 'width', 'span'])
        );
    }

    private static function baseConfig(): HtmlSanitizerConfig
    {
        return (new HtmlSanitizerConfig)
            ->allowSafeElements()
            ->allowElement('a', ['href', 'title', 'target', 'rel'])
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height'])
            ->allowLinkSchemes(['https', 'http', 'mailto'])
            ->allowMediaSchemes(['https', 'http'])
            ->forceAttribute('a', 'rel', 'noopener noreferrer')
            ->withMaxInputLength(500_000);
    }
}
