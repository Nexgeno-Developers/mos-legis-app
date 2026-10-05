<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The archive search box was removed from the home page hero, so its placeholder field goes too.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_metas')
            ->whereIn('page_id', DB::table('pages')->where('template', 'home')->pluck('id'))
            ->where('meta_key', 'search_placeholder')
            ->delete();
    }

    public function down(): void
    {
        // Not restored.
    }
};
