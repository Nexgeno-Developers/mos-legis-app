<?php

use App\Models\Page;
use App\Support\PageTemplates;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The home page gets its own "Home" template: all its wording (hero, section headings, the
 * "Why publish" points, the steps, buttons and the closing band) is edited in Admin → Pages → Home.
 * Today's wording is stored on the page; nothing an admin already saved is changed.
 */
return new class extends Migration
{
    private const TEMPLATES = "'layout','teams','patron','paper_winner','contact','career','jobs','plagiarism_checker','submit','archive'";

    public function up(): void
    {
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.",'home') NOT NULL");
        DB::table('pages')->where('slug', 'home')->update(['template' => 'home']);

        Page::where('template', 'home')->get()->each(fn (Page $page) => PageTemplates::storeMissingDefaults($page));
    }

    public function down(): void
    {
        DB::table('pages')->where('template', 'home')->update(['template' => 'layout']);
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.') NOT NULL');
    }
};
