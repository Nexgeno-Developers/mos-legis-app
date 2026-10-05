<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ManuscriptFee;
use App\Models\Page;
use App\Services\Manuscripts\FeeCalculator;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SOW C.08 — Submit page: categories & word limits, formatting rules, checklist,
 * fee structure, and the step form for signed-in authors.
 */
class SubmitPageController extends Controller
{
    public function __invoke(Request $request, FeeCalculator $fees, Page $page): View
    {
        $authorCategories = AuthorCategory::active()->orderBy('name')->get(['id', 'name']);
        $contentCategories = ContentCategory::active()->with('currentTheme')->orderBy('name')->get();
        $matrix = ManuscriptFee::all()->mapWithKeys(fn (ManuscriptFee $f) => ["{$f->author_category_id}-{$f->content_category_id}" => (float) $f->fees]);

        return view('site.submit', [
            'page' => $page,
            'authorCategories' => $authorCategories,
            'contentCategories' => $contentCategories,
            'fees' => $matrix,
            'prescreeningFee' => $fees->prescreeningFee(),
            'taxRate' => settings()->float('payment.tax_rate_percent'),
            'isAuthor' => (bool) $request->user()?->isAuthor(),
            'profile' => $request->user()?->authorProfile,
        ]);
    }
}
