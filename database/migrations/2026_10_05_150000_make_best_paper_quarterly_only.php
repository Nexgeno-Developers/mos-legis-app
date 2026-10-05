<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Best Paper is now awarded per quarter only.
 * - Existing monthly awards become the award of their quarter (August 2026 → Q3 2026). If that quarter
 *   already has a winner, the monthly award is removed (one winner per quarter).
 * - The Best Paper page's default wording ("each month", "next month") is updated where an admin hasn't changed it.
 */
return new class extends Migration
{
    private const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    private const WORDING = [
        'winner_choose_desc' => [
            'Each month, the editorial board selects one published manuscript as the Best Paper of the month, on the recommendation of the assigned reviewer and a final board vote.',
            'Each quarter, the editorial board selects one published manuscript as the Best Paper of the quarter, on the recommendation of the assigned reviewer and a final board vote.',
        ],
        'be_considered_desc' => [
            'Every manuscript accepted and published in a given month is automatically eligible. To be considered, simply submit your manuscript for publication.',
            'Every manuscript accepted and published in a given quarter is automatically eligible. To be considered, simply submit your manuscript for publication.',
        ],
        'be_considered_title' => ['Be Considered Next Month', 'Be Considered Next Quarter'],
    ];

    public function up(): void
    {
        // Latest monthly award of a quarter wins the conversion.
        $monthly = DB::table('best_paper_awards')->where('period_type', 'monthly')->orderByDesc('selected_at')->orderByDesc('id')->get();

        foreach ($monthly as $award) {
            $quarter = 'Q'.(intdiv((int) array_search($award->award_month, self::MONTHS, true), 3) + 1);
            $taken = DB::table('best_paper_awards')->where('period_type', 'quarterly')
                ->where('award_quarter', $quarter)->where('award_year', $award->award_year)->exists();

            if ($taken) {
                DB::table('best_paper_awards')->where('id', $award->id)->delete();
            } else {
                DB::table('best_paper_awards')->where('id', $award->id)
                    ->update(['period_type' => 'quarterly', 'award_month' => null, 'award_quarter' => $quarter]);
            }
        }

        $pageIds = DB::table('pages')->where('template', 'paper_winner')->pluck('id');
        foreach (self::WORDING as $key => [$old, $new]) {
            DB::table('page_metas')->whereIn('page_id', $pageIds)->where('meta_key', $key)->where('meta_value', $old)->update(['meta_value' => $new]);
        }
        DB::table('pages')->whereIn('id', $pageIds)->where('seo_description', 'Monthly best paper prize, criteria and past winners.')
            ->update(['seo_description' => 'Quarterly best paper prize, criteria and past winners.']);
    }

    public function down(): void
    {
        // Monthly awards are not restored.
    }
};
