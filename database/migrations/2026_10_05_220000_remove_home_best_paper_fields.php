<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The Best Paper spotlight was removed from the home page, so its button fields go too.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_metas')
            ->whereIn('page_id', DB::table('pages')->where('template', 'home')->pluck('id'))
            ->whereIn('meta_key', ['winner_primary', 'winner_secondary'])
            ->delete();
    }

    public function down(): void
    {
        // Not restored.
    }
};
