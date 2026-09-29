<?php

namespace App\Http\Controllers\Site;

use App\Enums\AwardPeriodType;
use App\Http\Controllers\Controller;
use App\Models\BestPaperAward;
use App\Models\Blog;
use App\Models\ContentCategory;
use App\Models\ManuscriptSubmission;
use App\Support\PublicPages;
use Illuminate\View\View;

/**
 * SOW C.09 — home: hero, categories, latest publications, best paper, blogs, submission call.
 */
class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('site.home', [
            'page' => PublicPages::bySlug('home'),
            'categories' => ContentCategory::active()->orderBy('name')->get(['id', 'name']),
            'publications' => ManuscriptSubmission::published()
                ->with(['author:id,name', 'contentCategory:id,name', 'theme'])
                ->latest('published_at')->limit(6)->get(),
            'winner' => BestPaperAward::with('submission.author:id,name', 'submission.contentCategory:id,name')
                ->where('period_type', AwardPeriodType::Monthly)
                ->get()
                ->sortByDesc(fn (BestPaperAward $a) => $a->award_year * 100 + array_search($a->award_month, BestPaperAward::MONTHS, true))
                ->first(),
            'blogs' => Blog::live()->with('category:id,category_name')->latest('publish_date')->limit(3)->get(),
        ]);
    }
}
