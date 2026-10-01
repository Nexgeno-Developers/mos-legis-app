<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

/**
 * Built-in website pages that a menu can link to ("Website page" link type).
 */
final class SiteRoutes
{
    /** @var array<string, string> route name => label */
    public const OPTIONS = [
        'home' => 'Home',
        'about' => 'About the Journal',
        'editorial-board' => 'Editorial Board',
        'patrons' => 'Patrons',
        'submit' => 'Submit a Manuscript',
        'archive.index' => 'Archive',
        'best-paper' => 'Best Paper',
        'plagiarism-checker' => 'Plagiarism Checker',
        'blogs.index' => 'Blogs',
        'jobs.index' => 'Job Board',
        'careers' => 'Careers at MOS Legis',
        'contact' => 'Contact',
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
