<?php

use App\Models\Page;
use App\Support\PageTemplates;
use Illuminate\Database\Migrations\Migration;

/**
 * Section headings that were fixed in the page designs (Best Paper, Contact, Careers, Patrons) are now
 * fields in Admin → Pages. Today's wording is stored on each page; existing values are never changed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Page::all()->each(fn (Page $page) => PageTemplates::storeMissingDefaults($page));
    }

    public function down(): void
    {
        // Wording admins may have edited is kept.
    }
};
