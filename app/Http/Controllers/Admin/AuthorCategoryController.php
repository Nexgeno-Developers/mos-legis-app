<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthorCategoryRequest;
use App\Models\AuthorCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

/**
 * SOW A.12 — Manuscript Author Categories.
 */
class AuthorCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:author-categories.view', only: ['index']),
            new Middleware('can:author-categories.edit', only: ['toggleStatus']),
            new Middleware('can:author-categories.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $categories = AuthorCategory::query()
            ->withCount('fees')
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->enum('status', RecordStatus::class), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.author-categories.index', compact('categories'));
    }

    public function store(AuthorCategoryRequest $request): RedirectResponse
    {
        $category = AuthorCategory::create($request->validated());
        activity()->log('Author Categories', 'Created author category', $category, $request->validated());

        return back()->with('success', 'Author category created. Set its fees in Manuscript Fees.');
    }

    public function update(AuthorCategoryRequest $request, AuthorCategory $authorCategory): RedirectResponse
    {
        $authorCategory->update($request->validated());
        activity()->log('Author Categories', 'Updated author category', $authorCategory, $request->validated());

        return back()->with('success', 'Author category updated.');
    }

    public function toggleStatus(AuthorCategory $authorCategory): RedirectResponse
    {
        $authorCategory->update(['status' => $authorCategory->isActive() ? RecordStatus::Inactive : RecordStatus::Active]);
        activity()->log('Author Categories', 'Changed status to '.$authorCategory->status->value, $authorCategory);

        return back()->with('success', "{$authorCategory->name} is now {$authorCategory->status->value}.");
    }

    public function destroy(AuthorCategory $authorCategory): RedirectResponse
    {
        if ($authorCategory->submissionsExist()) {
            return back()->with('error', 'This category is used by manuscripts or author profiles — deactivate it instead.');
        }

        activity()->log('Author Categories', 'Deleted author category', $authorCategory, ['name' => $authorCategory->name]);
        $authorCategory->delete();

        return back()->with('success', 'Author category deleted.');
    }
}
