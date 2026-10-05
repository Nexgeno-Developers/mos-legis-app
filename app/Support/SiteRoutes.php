<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Built-in website pages that a menu can link to ("Website page" link type).
 * CMS pages (About, Submit, Contact…) are linked with the "Page" link type instead, so links follow their slug and status.
 */
final class SiteRoutes
{
    /** @var array<string, string> route name => label */
    public const OPTIONS = [
        'home' => 'Home',
        'archive.index' => 'Archive',
        'blogs.index' => 'Blogs',
        'login' => 'Author sign in',
        'register' => 'Author registration',
    ];

    /** @return array<string, string> */
    public static function options(): array
    {
        return array_filter(self::OPTIONS, fn (string $name) => Route::has($name), ARRAY_FILTER_USE_KEY);
    }

    public static function label(?string $name): ?string
    {
        return self::OPTIONS[$name] ?? null;
    }
}
