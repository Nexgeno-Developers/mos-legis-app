<?php

use Database\Seeders\PageSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pages show only what is saved in Admin → Pages (no built-in fallback text), so the Submit page's
 * wording is stored on the page itself. Only fields the page doesn't have yet are added.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('pages')->where('template', 'submit')->pluck('id') as $pageId) {
            $existing = DB::table('page_metas')->where('page_id', $pageId)->pluck('meta_key')->all();

            foreach (PageSeeder::submitMetas() as $key => ['type' => $type, 'value' => $value]) {
                if (in_array($key, $existing, true)) {
                    continue;
                }

                DB::table('page_metas')->insert([
                    'page_id' => $pageId, 'meta_key' => $key, 'meta_type' => $type,
                    'meta_value' => $type === 'json' ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Wording that admins may have edited is kept.
    }
};
