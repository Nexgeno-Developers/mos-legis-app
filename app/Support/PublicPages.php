<?php

namespace App\Support;

use App\Enums\PageTemplate;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;

/**
 * CMS pages on the public site. Every page lives at /{slug} and is visible only while published;
 * Home is the one fixed page (/). Pages with a special template are found by template, so their
 * slug can be changed freely in Admin → Pages.
 */
final class PublicPages
{
    private const CACHE_KEY = 'public-pages.slugs';

    public static function bySlug(string $slug): ?Page
    {
        return Page::published()->with('metas')->where('slug', $slug)->first();
    }

    /** Address of the (first) page with this template; Home if there is none. */
    public static function url(PageTemplate $template): string
    {
        $slug = Cache::rememberForever(self::CACHE_KEY, fn () => Page::where('template', '!=', PageTemplate::Layout)
            ->orderByDesc('id')->pluck('slug', 'template')->all())[$template->value] ?? null;

        return $slug ? url($slug) : route('home');
    }

    /** Called whenever a page is saved or deleted. */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
