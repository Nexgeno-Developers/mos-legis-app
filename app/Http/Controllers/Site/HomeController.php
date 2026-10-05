<?php

namespace App\Http\Controllers\Site;

use App\Enums\ManuscriptStage;
use App\Http\Controllers\Controller;
use App\Models\BestPaperAward;
use App\Models\Blog;
use App\Models\ContentCategory;
use App\Models\ManuscriptSubmission;
use App\Support\PublicPages;
use Illuminate\View\View;

/**
 * SOW C.09 — home: hero with archive search, journal at a glance, why publish with us, how it works,
 * browse by category, latest publications, blog, submission call.
 */
class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('site.home', [
            'page' => PublicPages::bySlug('home'),
            // Categories with their number of published articles (for "Browse by category").
            'categories' => ContentCategory::active()->orderBy('name')
                ->withCount(['submissions' => fn ($q) => $q->where('stage', ManuscriptStage::Published)])
                ->get(['id', 'name', 'min_word_limit', 'max_word_limit']),
            'publications' => ManuscriptSubmission::published()
                ->with(['author:id,name', 'contentCategory:id,name', 'theme', 'awards:id,manuscript_submission_id'])
                ->latest('published_at')->limit(3)->get(),
            // Journal at a glance.
            'stats' => [
                'articles' => ManuscriptSubmission::published()->count(),
                'authors' => ManuscriptSubmission::published()->distinct()->count('user_id'),
                'categories' => ContentCategory::active()->count(),
                'awards' => BestPaperAward::count(),
            ],
            'blogs' => Blog::live()->with('category:id,category_name')->latest('publish_date')->limit(3)->get(),
        ]);
    }
}
