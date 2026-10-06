<?php

use App\Models\Page;
use Illuminate\Database\Migrations\Migration;

/**
 * Submit, Plagiarism Checker and Contact get fixed addresses (/submit, /plagiarism-checker, /contact),
 * like Home and the Journal Archive. A page whose slug had been changed is moved back; its other
 * address keeps working as a redirect. A different page using one of these slugs is renamed first.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (Page::whereIn('template', ['submit', 'plagiarism_checker', 'contact'])->orderBy('id')->get() as $page) {
            $fixed = $page->fixedSlug();
            if ($page->slug === $fixed) {
                continue;
            }

            Page::where('slug', $fixed)->whereKeyNot($page->id)->get()
                ->each(fn (Page $other) => $other->update(['slug' => $fixed.'-'.$other->id]));

            $page->update(['slug' => $fixed]);
        }
    }

    public function down(): void
    {
        // Addresses stay as they are.
    }
};
