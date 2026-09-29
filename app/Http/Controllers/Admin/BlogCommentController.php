<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogCommentRequest;
use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * SOW A.08 — Blog Comments moderation, including admin replies (threaded via parent_id).
 */
class BlogCommentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:blog-comments.view', only: ['index']),
            new Middleware('can:blog-comments.create', only: ['reply']),
            new Middleware('can:blog-comments.edit', only: ['update', 'moderate']),
            new Middleware('can:blog-comments.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $comments = BlogComment::query()
            ->with(['blog:id,blog_title,slug', 'parent:id,name'])
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('comment', 'like', "%{$search}%")))
            ->when($request->string('blog')->trim()->value(), fn ($q, $blog) => $q->whereHas('blog', fn ($q) => $q->where('blog_title', 'like', "%{$blog}%")))
            ->when($request->enum('status', CommentStatus::class), fn ($q, $status) => $q->where('status', $status))
            ->orderByRaw('status = ? desc', [CommentStatus::Pending->value])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog-comments.index', [
            'comments' => $comments,
            'blogs' => Blog::orderBy('blog_title')->pluck('blog_title', 'id'),
        ]);
    }

    public function store(BlogCommentRequest $request): RedirectResponse
    {
        abort_unless($request->user()->can('blog-comments.create'), 403);

        $comment = BlogComment::create($request->validated() + [
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ] + $this->approvalFields($request->validated('status')));

        activity()->log('Blog Comments', 'Created comment', $comment, $request->validated());

        return back()->with('success', 'Comment added.');
    }

    public function update(BlogCommentRequest $request, BlogComment $blogComment): RedirectResponse
    {
        $blogComment->update($request->safe()->only(['comment', 'status']) + $this->approvalFields($request->validated('status'), $blogComment));
        activity()->log('Blog Comments', 'Updated comment', $blogComment, $request->validated());

        return back()->with('success', 'Comment updated.');
    }

    /** Approve / reject (the SOW's activate / deactivate). */
    public function moderate(Request $request, BlogComment $blogComment): RedirectResponse
    {
        $status = $request->validate(['status' => ['required', Rule::enum(CommentStatus::class)]])['status'];

        $blogComment->update(['status' => $status] + $this->approvalFields($status, $blogComment));
        activity()->log('Blog Comments', 'Comment '.strtolower($blogComment->status->value), $blogComment);

        return back()->with('success', "Comment {$blogComment->status->value}.");
    }

    public function reply(Request $request, BlogComment $blogComment): RedirectResponse
    {
        $data = $request->validate(['comment' => ['required', 'string', 'max:5000']]);

        $reply = $blogComment->replies()->create([
            'blog_id' => $blogComment->blog_id,
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'comment' => $data['comment'],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ] + $this->approvalFields(CommentStatus::Approved->value));

        $blogComment->update(['replied_by' => $request->user()->id] + ($blogComment->status === CommentStatus::Pending
            ? $this->approvalFields(CommentStatus::Approved->value)
            : []));

        activity()->log('Blog Comments', 'Replied to comment', $reply, ['parent_id' => $blogComment->id]);

        return back()->with('success', 'Reply posted.');
    }

    public function destroy(BlogComment $blogComment): RedirectResponse
    {
        activity()->log('Blog Comments', 'Deleted comment', $blogComment, ['name' => $blogComment->name]);
        $blogComment->delete();

        return back()->with('success', 'Comment deleted.');
    }

    private function approvalFields(string $status, ?BlogComment $comment = null): array
    {
        if ($status === CommentStatus::Approved->value) {
            return $comment?->approved_at ? [] : ['status' => $status, 'approved_by' => auth()->id(), 'approved_at' => now()];
        }

        return ['status' => $status];
    }
}
