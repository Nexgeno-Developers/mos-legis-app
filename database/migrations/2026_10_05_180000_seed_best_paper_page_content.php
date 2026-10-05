<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Best Paper page's former info boxes, as headings and paragraphs in the page Content
 * (editable in Admin → Pages). Only filled in where the page has no content yet.
 */
return new class extends Migration
{
    public const CONTENT = '<h2>How Winners Are Chosen</h2>'
        .'<p>Each quarter, the editorial board selects one published manuscript as the Best Paper of the quarter, on the recommendation of the assigned reviewer and a final board vote.</p>'
        .'<h2>The Prize</h2>'
        .'<p>The winning author receives a certificate of recognition and a featured citation on the journal’s home page. A cash prize may also be awarded for the quarter.</p>'
        .'<h2>Be Considered Next Quarter</h2>'
        .'<p>Every manuscript accepted and published in a given quarter is automatically eligible. To be considered, simply submit your manuscript for publication.</p>';

    public function up(): void
    {
        DB::table('pages')->where('template', 'paper_winner')
            ->where(fn ($q) => $q->whereNull('content')->orWhere('content', ''))
            ->update(['content' => self::CONTENT, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('pages')->where('template', 'paper_winner')->where('content', self::CONTENT)->update(['content' => null]);
    }
};
