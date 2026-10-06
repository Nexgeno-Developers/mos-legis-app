<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ManuscriptFeeRequest;
use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ManuscriptCoAuthorFee;
use App\Models\ManuscriptFee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * SOW A.15 — fee matrix: author categories as rows, content categories as columns.
 * New categories appear automatically; a blank cell means the combination is not offered.
 * Below it, the co-author surcharge per content category (added to the publication fee).
 */
class ManuscriptFeeController extends Controller
{
    public function index(): View
    {
        Gate::authorize('fees.view');

        return view('admin.fees.index', [
            'authorCategories' => AuthorCategory::orderBy('name')->get(['id', 'name', 'status']),
            'contentCategories' => ContentCategory::orderBy('name')->get(['id', 'name', 'status']),
            'fees' => ManuscriptFee::all()->mapWithKeys(fn (ManuscriptFee $fee) => [
                "{$fee->author_category_id}-{$fee->content_category_id}" => $fee->fees,
            ]),
            'coAuthorFees' => ManuscriptCoAuthorFee::all()->keyBy('content_category_id'),
        ]);
    }

    public function update(ManuscriptFeeRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            foreach ($request->validated('fees', []) as $authorCategoryId => $row) {
                foreach ($row as $contentCategoryId => $amount) {
                    $keys = ['author_category_id' => $authorCategoryId, 'content_category_id' => $contentCategoryId];

                    if ($amount === null || $amount === '') {
                        ManuscriptFee::where($keys)->delete();
                    } else {
                        ManuscriptFee::updateOrCreate($keys, ['fees' => $amount]);
                    }
                }
            }

            // Co-author surcharge: both cells blank removes the row (no surcharge for that category).
            foreach ($request->validated('coauthor_fees', []) as $contentCategoryId => $rates) {
                $firstTwo = $rates['first_two'] ?? null;
                $additional = $rates['additional'] ?? null;

                if ($firstTwo === null && $additional === null) {
                    ManuscriptCoAuthorFee::where('content_category_id', $contentCategoryId)->delete();
                } else {
                    ManuscriptCoAuthorFee::updateOrCreate(
                        ['content_category_id' => $contentCategoryId],
                        ['first_two_fee' => $firstTwo ?? 0, 'additional_fee' => $additional ?? 0],
                    );
                }
            }
        });

        activity()->log('Manuscript Fees', 'Updated fee matrix', null, [
            'fees' => $request->validated('fees'),
            'coauthor_fees' => $request->validated('coauthor_fees'),
        ]);

        return back()->with('success', 'Manuscript fees saved.');
    }
}
