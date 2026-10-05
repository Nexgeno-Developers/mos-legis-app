<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The three info boxes on the Best Paper page (how winners are chosen, the prize, be considered)
 * were removed; the page's own Content (Admin → Pages) is shown at the end instead.
 */
return new class extends Migration
{
    private const KEYS = [
        'cards_label', 'winner_choose_title', 'winner_choose_desc', 'prize_title', 'prize_desc',
        'be_considered_title', 'be_considered_desc', 'be_considered_button',
    ];

    public function up(): void
    {
        DB::table('page_metas')
            ->whereIn('page_id', DB::table('pages')->where('template', 'paper_winner')->pluck('id'))
            ->whereIn('meta_key', self::KEYS)
            ->delete();
    }

    public function down(): void
    {
        // The removed box texts are not restored.
    }
};
