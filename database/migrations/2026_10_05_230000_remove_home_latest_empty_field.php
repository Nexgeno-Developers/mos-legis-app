<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The "nothing published yet" message on the home page is fixed text again, so its field goes.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_metas')
            ->whereIn('page_id', DB::table('pages')->where('template', 'home')->pluck('id'))
            ->where('meta_key', 'latest_empty')
            ->delete();
    }

    public function down(): void
    {
        // Not restored.
    }
};
