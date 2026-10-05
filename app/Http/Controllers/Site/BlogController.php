<?php

namespace App\Http\Controllers\Site;

use App\Enums\CommentStatus;
use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * SOW C.02 — blog listing, detail and comments (signed-in users). Comments go live at once or after
 * admin approval ("Blog comment approval required" setting); writers can delete their own comments.
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

        // Approved comments, plus the viewer's own comments that are still awaiting approval.
        $visible = fn ($q) => $q->where(fn ($q) => $q->where('status', CommentStatus::Approved)
            ->when($request->user(), fn ($q, $user) => $q->orWhere(fn ($q) => $q->where('user_id', $user->id)->where('status', CommentStatus::Pending))));

        $comments = $blog->comments()->whereNull('parent_id')->tap($visible)
            ->with(['replies' => fn ($q) => $visible($q)->oldest()])
            ->oldest()->get();

        return view('site.blogs.show', [
            'blog' => $blog,
            'comments' => $comments,
            'commentCount' => $comments->sum(fn ($c) => 1 + $c->replies->count()),
            'related' => Blog::live()->where('category_id', $blog->category_id)->whereKeyNot($blog->id)->latest('publish_date')->limit(3)->get(),
        ]);
    }

    public function comment(Request $request, string $slug): RedirectResponse
    {
        $blog = Blog::live()->where('slug', $slug)->firstOrFail();
        $data = $request->validate([
            'comment' => ['required', 'string', 'min:3', 'max:3000'],
            'parent_id' => ['nullable', 'integer', Rule::exists('blog_comments', 'id')->where('blog_id', $blog->id)->whereNull('parent_id')],
        ]);

        $user = $request->user();
        $needsApproval = settings()->bool('approvals.blog_comment_approval_required');

        $comment = $blog->comments()->create([
            'parent_id' => $data['parent_id'] ?? null,
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'comment' => strip_tags($data['comment']),
            'status' => $needsApproval ? CommentStatus::Pending : CommentStatus::Approved,
            'approved_at' => $needsApproval ? null : now(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return redirect()->to(route('blogs.show', $blog->slug).'#comment-'.$comment->id)->with('success', $needsApproval
            ? 'Thank you — your comment will appear once it has been approved.'
            : 'Your comment has been posted.');
    }

    /** Writers can remove their own comment; its replies go with it. */
    public function destroyComment(Request $request, string $slug, BlogComment $comment): RedirectResponse
    {
        $blog = Blog::where('slug', $slug)->firstOrFail();
        abort_unless($comment->blog_id === $blog->id && $comment->user_id === $request->user()->id, 403);

        DB::transaction(function () use ($comment) {
            $comment->replies()->delete();
            $comment->delete();
        });

        return redirect()->to(route('blogs.show', $blog->slug).'#comments')->with('success', 'Your comment has been deleted.');
    }
}
