<?php

namespace App\Http\Controllers\Site;

use App\Enums\CommentStatus;
use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * SOW C.02 — blog listing, detail and comments (logged-in users; moderated).
 */
class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $blogs = Blog::live()
            ->with(['category:id,category_name,slug', 'tags:id,tag_name,slug'])
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('blog_title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")))
            ->when($request->string('category')->value(), fn ($q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)))
            ->when($request->string('tag')->value(), fn ($q, $slug) => $q->whereHas('tags', fn ($q) => $q->where('slug', $slug)))
            ->when($request->date('from'), fn ($q, $from) => $q->whereDate('publish_date', '>=', $from))
            ->when($request->date('to'), fn ($q, $to) => $q->whereDate('publish_date', '<=', $to))
            ->orderByDesc('featured_post')
            ->latest('publish_date')
            ->paginate(9)
            ->withQueryString();

        return view('site.blogs.index', [
            'blogs' => $blogs,
            'categories' => BlogCategory::active()->orderBy('category_name')->pluck('category_name', 'slug'),
            'tags' => BlogTag::where('status', RecordStatus::Active)->orderBy('tag_name')->pluck('tag_name', 'slug'),
        ]);
    }

    public function show(Request $request, string $slug): View
    {
        $blog = Blog::live()->where('slug', $slug)
            ->with(['category:id,category_name,slug', 'tags:id,tag_name,slug', 'user.authorProfile'])
            ->firstOrFail();

        // Count one view per visitor session.
        $viewed = $request->session()->get('viewed_blogs', []);
        if (! in_array($blog->id, $viewed, true)) {
            $blog->increment('views');
            $request->session()->push('viewed_blogs', $blog->id);
        }

        return view('site.blogs.show', [
            'blog' => $blog,
            'comments' => $blog->approvedComments()->whereNull('parent_id')
                ->with(['replies' => fn ($q) => $q->where('status', CommentStatus::Approved)->oldest()])
                ->oldest()->get(),
            'related' => Blog::live()->where('category_id', $blog->category_id)->whereKeyNot($blog->id)->latest('publish_date')->limit(3)->get(),
        ]);
    }

    public function comment(Request $request, string $slug): RedirectResponse
    {
        $blog = Blog::live()->where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'comment' => ['required', 'string', 'min:3', 'max:3000'],
            'parent_id' => ['nullable', 'integer', 'exists:blog_comments,id'],
        ]);

        $user = $request->user();
        $blog->comments()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'comment' => strip_tags($data['comment']),
            'status' => CommentStatus::Pending,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return back()->with('success', 'Thank you — your comment will appear once it has been approved.');
    }
}
