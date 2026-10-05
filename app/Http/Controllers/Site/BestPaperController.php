<?php

namespace App\Http\Controllers\Site;

use App\Enums\AwardPeriodType;
use App\Http\Controllers\Controller;
use App\Models\BestPaperAward;
use App\Models\ContentCategory;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SOW C.04 — current quarterly winner, prize/criteria copy from the CMS Winner layout,
 * past winners filtered by year and category, "Be considered next month" CTA.
 */
class BestPaperController extends Controller
{
    public function __invoke(Request $request, Page $page): View
    {
        $awards = BestPaperAward::query()
            ->with(['submission.author:id,name', 'submission.contentCategory:id,name', 'submission.theme'])
            ->where('period_type', AwardPeriodType::Quarterly)
            ->get()
            ->sortByDesc(fn (BestPaperAward $a) => $a->periodOrder())
            ->values();

        // The latest quarter's winner; earlier winners are listed below.
        $current = $awards->first();

        $past = $awards->reject(fn ($a) => $current && $a->is($current))
            ->when($request->integer('year'), fn ($c, $year) => $c->where('award_year', $year))
            ->when($request->integer('category'), fn ($c, $id) => $c->filter(fn ($a) => $a->submission->content_category_id === $id));

        return view('site.best-paper', [
            'page' => $page,
            'current' => $current,
            'past' => $past,
            'years' => $awards->pluck('award_year')->unique()->sortDesc()->values(),
            'categories' => ContentCategory::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
