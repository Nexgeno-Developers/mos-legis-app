<?php

namespace App\Http\Controllers\Site;

use App\Enums\AwardPeriodType;
use App\Enums\PageTemplate;
use App\Http\Controllers\Controller;
use App\Models\BestPaperAward;
use App\Models\ContentCategory;
use App\Support\PublicPages;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SOW C.04 — current monthly winner, prize/criteria copy from the CMS Winner layout,
 * past winners filtered by year and category, "Be considered next month" CTA.
 */
class BestPaperController extends Controller
{
    public function __invoke(Request $request): View
    {
        $awards = BestPaperAward::query()
            ->with(['submission.author:id,name', 'submission.contentCategory:id,name', 'submission.theme'])
            ->get()
            ->sortByDesc(fn (BestPaperAward $a) => $a->award_year * 100 + ($a->period_type === AwardPeriodType::Monthly
                ? array_search($a->award_month, BestPaperAward::MONTHS, true) + 1
                : (int) substr((string) $a->award_quarter, 1) * 3))
            ->values();

        $current = $awards->firstWhere('period_type', AwardPeriodType::Monthly);

        $past = $awards->reject(fn ($a) => $current && $a->is($current))
            ->when($request->integer('year'), fn ($c, $year) => $c->where('award_year', $year))
            ->when($request->integer('category'), fn ($c, $id) => $c->filter(fn ($a) => $a->submission->content_category_id === $id));

        return view('site.best-paper', [
            'page' => PublicPages::byTemplate(PageTemplate::PaperWinner, 'best-paper-winners'),
            'current' => $current,
            'past' => $past,
            'years' => $awards->pluck('award_year')->unique()->sortDesc()->values(),
            'categories' => ContentCategory::orderBy('name')->pluck('name', 'id'),
        ]);
    }
}
