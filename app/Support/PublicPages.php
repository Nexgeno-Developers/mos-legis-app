<?php

namespace App\Support;

use App\Enums\PageTemplate;
use App\Models\Page;

/**
 * Loads published CMS pages for the public site by template or slug.
 */
final class PublicPages
{
    public static function bySlug(string $slug): ?Page
    {
        return Page::published()->with('metas')->where('slug', $slug)->first();
    }

    public static function byTemplate(PageTemplate $template, ?string $preferredSlug = null): ?Page
    {
        return Page::published()->with('metas')
            ->where('template', $template)
            ->when($preferredSlug, fn ($q) => $q->orderByRaw('slug = ? desc', [$preferredSlug]))
            ->oldest('id')
            ->first();
    }
}
