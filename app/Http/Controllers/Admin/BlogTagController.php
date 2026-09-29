<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogTagRequest;
use App\Models\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

/**
 * SOW A.07 — Blog Tags.
 */
class BlogTagController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:blog-tags.view', only: ['index']),
            new Middleware('can:blog-tags.create', only: ['duplicate']),
            new Middleware('can:blog-tags.edit', only: ['toggleStatus']),
            new Middleware('can:blog-tags.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $tags = BlogTag::query()
            ->withCount('blogs')
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('tag_name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
            ->when($request->enum('status', RecordStatus::class), fn ($q, $status) => $q->where('status', $status))
            ->orderBy('tag_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog-tags.index', compact('tags'));
    }

    public function store(BlogTagRequest $request): RedirectResponse
    {
        $tag = BlogTag::create($request->validated() + ['user_id' => $request->user()->id]);
        activity()->log('Blog Tags', 'Created blog tag', $tag, $request->validated());

        return back()->with('success', 'Blog tag created.');
    }

    public function update(BlogTagRequest $request, BlogTag $blogTag): RedirectResponse
    {
        $blogTag->update($request->validated());
        activity()->log('Blog Tags', 'Updated blog tag', $blogTag, $request->validated());

        return back()->with('success', 'Blog tag updated.');
    }

    public function toggleStatus(BlogTag $blogTag): RedirectResponse
    {
        $blogTag->update(['status' => $blogTag->isActive() ? RecordStatus::Inactive : RecordStatus::Active]);
        activity()->log('Blog Tags', 'Changed status to '.$blogTag->status->value, $blogTag);

        return back()->with('success', "{$blogTag->tag_name} is now {$blogTag->status->value}.");
    }

    public function duplicate(BlogTag $blogTag): RedirectResponse
    {
        $slug = $blogTag->slug.'-copy';
        $i = 2;
        while (BlogTag::where('slug', $slug)->exists()) {
            $slug = $blogTag->slug.'-copy-'.$i++;
        }

        $copy = BlogTag::create([
            'tag_name' => $blogTag->tag_name.' (Copy)',
            'slug' => $slug,
            'status' => RecordStatus::Inactive,
            'user_id' => auth()->id(),
        ]);
        activity()->log('Blog Tags', 'Duplicated blog tag', $copy, ['source' => $blogTag->id]);

        return back()->with('success', 'Blog tag duplicated (inactive).');
    }

    public function destroy(BlogTag $blogTag): RedirectResponse
    {
        activity()->log('Blog Tags', 'Deleted blog tag', $blogTag, ['name' => $blogTag->tag_name]);
        $blogTag->delete();

        return back()->with('success', 'Blog tag deleted and removed from its posts.');
    }
}
