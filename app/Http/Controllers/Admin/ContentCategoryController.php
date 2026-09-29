<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ContentCategoryRequest;
use App\Models\ContentCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

/**
 * SOW A.13 — Manuscript Content Categories.
 */
class ContentCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:content-categories.view', only: ['index']),
            new Middleware('can:content-categories.edit', only: ['toggleStatus']),
            new Middleware('can:content-categories.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $categories = ContentCategory::query()
            ->withCount(['themes', 'reviewers'])
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($request->enum('status', RecordStatus::class), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.content-categories.index', compact('categories'));
    }

    public function store(ContentCategoryRequest $request): RedirectResponse
    {
        $category = ContentCategory::create($request->validated());
        activity()->log('Content Categories', 'Created content category', $category, $request->validated());

        return back()->with('success', 'Content category created. Set its fees in Manuscript Fees.');
    }

    public function update(ContentCategoryRequest $request, ContentCategory $contentCategory): RedirectResponse
    {
        $contentCategory->update($request->validated());
        activity()->log('Content Categories', 'Updated content category', $contentCategory, $request->validated());

        return back()->with('success', 'Content category updated.');
    }

    public function toggleStatus(ContentCategory $contentCategory): RedirectResponse
    {
        $contentCategory->update(['status' => $contentCategory->isActive() ? RecordStatus::Inactive : RecordStatus::Active]);
        activity()->log('Content Categories', 'Changed status to '.$contentCategory->status->value, $contentCategory);

        return back()->with('success', "{$contentCategory->name} is now {$contentCategory->status->value}.");
    }

    public function destroy(ContentCategory $contentCategory): RedirectResponse
    {
        if ($contentCategory->submissions()->exists()) {
            return back()->with('error', 'This category has manuscripts — deactivate it instead.');
        }

        activity()->log('Content Categories', 'Deleted content category', $contentCategory, ['name' => $contentCategory->name]);
        $contentCategory->delete();

        return back()->with('success', 'Content category deleted.');
    }
}
