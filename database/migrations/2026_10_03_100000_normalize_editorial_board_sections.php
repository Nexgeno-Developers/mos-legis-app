<?php

use App\Support\PageTemplates;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Editorial board members: the free-text "group" becomes a fixed section key
 * (founder / board / advisory) so the public page is split into proper sections.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('page_metas')->where('meta_key', 'members')->orderBy('id')->each(function ($meta) {
            $members = json_decode((string) $meta->meta_value, true);
            if (! is_array($members)) {
                return;
            }

            $members = array_map(fn ($m) => ['group' => PageTemplates::teamSection($m['group'] ?? null)] + (array) $m, $members);
            DB::table('page_metas')->where('id', $meta->id)->update(['meta_value' => json_encode($members, JSON_UNESCAPED_UNICODE)]);
        });
    }

    public function down(): void
    {
        // Section keys remain valid; nothing to undo.
    }
};
