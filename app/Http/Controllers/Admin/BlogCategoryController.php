<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCategoryRequest;
use App\Models\BlogCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * SOW A.06 — Blog Categories.
 */
class BlogCategoryController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:blog-categories.view', only: ['index']),
            new Middleware('can:blog-categories.create', only: ['duplicate']),
            new Middleware('can:blog-categories.edit', only: ['toggleStatus']),
            new Middleware('can:blog-categories.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $categories = BlogCategory::query()
            ->withCount('blogs')
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('category_name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
            ->when($request->enum('status', RecordStatus::class), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('category_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.blog-categories.index', compact('categories'));
    }

    public function store(BlogCategoryRequest $request): RedirectResponse
    {
        $category = BlogCategory::create($request->validated());
        activity()->log('Blog Categories', 'Created blog category', $category, $request->validated());

        return back()->with('success', 'Blog category created.');
    }

    public function update(BlogCategoryRequest $request, BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->update($request->validated());
        activity()->log('Blog Categories', 'Updated blog category', $blogCategory, $request->validated());

        return back()->with('success', 'Blog category updated.');
    }

    public function toggleStatus(BlogCategory $blogCategory): RedirectResponse
    {
        $blogCategory->update(['status' => $blogCategory->isActive() ? RecordStatus::Inactive : RecordStatus::Active]);
        activity()->log('Blog Categories', 'Changed status to '.$blogCategory->status->value, $blogCategory);

        return back()->with('success', "{$blogCategory->category_name} is now {$blogCategory->status->value}.");
    }

    public function duplicate(BlogCategory $blogCategory): RedirectResponse
    {
        $copy = $blogCategory->replicate();
        $copy->category_name = $blogCategory->category_name.' (Copy)';
        $slug = $blogCategory->slug.'-copy';
        $i = 2;
        while (BlogCategory::where('slug', $slug)->exists()) {
            $slug = $blogCategory->slug.'-copy-'.$i++;
        }
        $copy->slug = Str::limit($slug, 140, '');
        $copy->status = RecordStatus::Inactive;
        $copy->save();

        activity()->log('Blog Categories', 'Duplicated blog category', $copy, ['source' => $blogCategory->id]);

        return back()->with('success', 'Blog category duplicated (inactive).');
    }

    public function destroy(BlogCategory $blogCategory): RedirectResponse
    {
        if ($blogCategory->blogs()->exists()) {
            return back()->with('error', 'This category has blog posts — move them or deactivate the category instead.');
        }

        activity()->log('Blog Categories', 'Deleted blog category', $blogCategory, ['name' => $blogCategory->category_name]);
        $blogCategory->delete();

        return back()->with('success', 'Blog category deleted.');
    }
}
