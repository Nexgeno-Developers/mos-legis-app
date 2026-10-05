<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Best Paper headings ("Current Winner", "Archive", "Past Winners") are fixed in the page design
 * again, so their page fields are removed.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_metas')
            ->whereIn('page_id', DB::table('pages')->where('template', 'paper_winner')->pluck('id'))
            ->whereIn('meta_key', ['current_label', 'past_label', 'past_heading'])
            ->delete();
    }

    public function down(): void
    {
        // Not restored.
    }
};
