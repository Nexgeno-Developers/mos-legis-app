<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Every CMS page is served at /{slug} and follows its own slug and status (only Home is fixed):
 * - the Submit page gets its own template, so it is found by template instead of by slug;
 * - header/footer menu links to these pages now point at the page itself (they follow its slug and status);
 * - the Best Paper page keeps its old address (/best-paper).
 */
return new class extends Migration
{
    private const TEMPLATES = "'layout','teams','patron','paper_winner','contact','career','jobs','plagiarism_checker'";

    /** Old built-in menu route => how its page is found. */
    private const MENU_ROUTES = [
        'about' => ['slug', 'about'],
        'editorial-board' => ['template', 'teams'],
        'patrons' => ['template', 'patron'],
        'submit' => ['template', 'submit'],
        'best-paper' => ['template', 'paper_winner'],
        'plagiarism-checker' => ['template', 'plagiarism_checker'],
        'jobs.index' => ['template', 'jobs'],
        'careers' => ['template', 'career'],
        'contact' => ['template', 'contact'],
    ];

    public function up(): void
    {
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.",'submit') NOT NULL");
        DB::table('pages')->where('slug', 'submit')->where('template', 'layout')->update(['template' => 'submit']);

        if (! DB::table('pages')->where('slug', 'best-paper')->exists()) {
            DB::table('pages')->where('slug', 'best-paper-winners')->update(['slug' => 'best-paper']);
        }

        foreach (self::MENU_ROUTES as $route => [$column, $value]) {
            $pageId = DB::table('pages')->where($column, $value)->orderBy('id')->value('id');

            if ($pageId) {
                DB::table('menu_items')->where('link_type', 'route')->where('route_name', $route)
                    ->update(['link_type' => 'page', 'page_id' => $pageId, 'route_name' => null]);
            }
        }
    }

    public function down(): void
    {
        DB::table('pages')->where('template', 'submit')->update(['template' => 'layout']);
        DB::statement('ALTER TABLE pages MODIFY template ENUM('.self::TEMPLATES.') NOT NULL');
    }
};
