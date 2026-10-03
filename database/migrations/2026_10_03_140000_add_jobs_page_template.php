<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Job Postings becomes a page managed in Admin → Pages ("Job postings" template): title, intro,
 * content and SEO are edited there; the filters and job list stay dynamic.
 */
return new class extends Migration
{
    private const TEMPLATES = "'layout','teams','patron','paper_winner','contact','career'";

    public function up(): void
    {
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.",'jobs') NOT NULL");

        if (! DB::table('pages')->where('template', 'jobs')->exists()) {
            DB::table('pages')->insert([
                'title' => 'Job Postings',
                'slug' => DB::table('pages')->where('slug', 'job-postings')->exists() ? 'job-postings-page' : 'job-postings',
                'template' => 'jobs',
                'status' => 'Published',
                'excerpt' => 'Vacancies submitted by firms, chambers and institutions. MOS Legis publishes listings as a service to readers and takes no part in recruitment.',
                'seo_title' => 'Job Postings',
                'seo_description' => 'Legal job listings from firms, chambers and institutions.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('pages')->where('template', 'jobs')->delete();
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.') NOT NULL');
    }
};
