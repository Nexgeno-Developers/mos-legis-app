<?php

use Database\Seeders\PageSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Journal Archive becomes a page in Admin → Pages ("Journal archive" template): title, intro
 * (under the breadcrumb), content and SEO are edited there; the search and article list stay dynamic.
 * Its address stays /archive (each manuscript's page lives under it). Created with today's wording.
 */
return new class extends Migration
{
    private const TEMPLATES = "'layout','teams','patron','paper_winner','contact','career','jobs','plagiarism_checker','submit'";

    public function up(): void
    {
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.",'archive') NOT NULL");

        if (DB::table('pages')->where('template', 'archive')->exists()) {
            return;
        }

        $page = PageSeeder::archivePage();
        // A page may already use the "archive" slug; the archive page always owns it.
        DB::table('pages')->where('slug', 'archive')->update(['slug' => 'archive-'.now()->format('YmdHis')]);

        DB::table('pages')->insert([
            'title' => $page['title'],
            'slug' => 'archive',
            'template' => 'archive',
            'status' => $page['status'],
            'excerpt' => $page['excerpt'],
            'seo_title' => $page['seo_title'],
            'seo_description' => $page['seo_description'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('pages')->where('template', 'archive')->delete();
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.') NOT NULL');
    }
};
