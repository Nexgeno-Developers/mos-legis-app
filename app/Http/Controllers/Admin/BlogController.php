<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Blogs\SaveBlog;
use App\Enums\BlogStatus;
use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\BlogRequest;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * SOW A.05 — Blogs Management (admin and author posts).
 */
class BlogController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Blog::class);

        $blogs = Blog::query()
            ->with('category:id,category_name')
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where(fn ($q) => $q
                ->where('blog_title', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%")
                ->orWhere('author_name', 'like', "%{$search}%")))
            ->when($request->integer('category_id'), fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->enum('status', BlogStatus::class), fn ($q, $status) => $q->where('status', $status))
            ->when($request->date('from'), fn ($q, $from) => $q->whereDate('publish_date', '>=', $from))
            ->when($request->date('to'), fn ($q, $to) => $q->whereDate('publish_date', '<=', $to))
            ->orderByRaw('status = ? desc', [BlogStatus::Pending->value])
            ->latest('publish_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.blogs.index', [
            'blogs' => $blogs,
            'categories' => BlogCategory::orderBy('category_name')->pluck('category_name', 'id'),
            'pendingCount' => Blog::where('status', BlogStatus::Pending)->count(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Blog::class);

        return view('admin.blogs.form', ['blog' => new Blog(['publish_date' => today(), 'status' => BlogStatus::Draft, 'author_name' => auth()->user()->name])] + $this->options());
    }

    public function store(BlogRequest $request, SaveBlog $saveBlog): RedirectResponse
    {
        $blog = $saveBlog->handle($request->validated(), $request->user());
        activity()->log('Blogs', 'Created blog', $blog, $request->safe()->only(['blog_title', 'status', 'category_id']));

        return redirect()->route('admin.blogs.index')->with('success', 'Blog created.');
    }

    public function edit(Blog $blog): View
    {
        Gate::authorize('update', $blog);
        $blog->load('tags:id');

        return view('admin.blogs.form', ['blog' => $blog] + $this->options());
    }

    public function update(BlogRequest $request, Blog $blog, SaveBlog $saveBlog): RedirectResponse
    {
        $saveBlog->handle($request->validated(), $request->user(), $blog);
        activity()->log('Blogs', 'Updated blog', $blog, $request->safe()->only(['blog_title', 'status', 'category_id']));

        return redirect()->route('admin.blogs.index')->with('success', 'Blog updated.');
    }

    /** Activate/deactivate (SOW): Published ⇄ Draft. Approves a pending author post. */
    public function toggleStatus(Blog $blog): RedirectResponse
    {
        Gate::authorize('update', $blog);

        $blog->update(['status' => $blog->status === BlogStatus::Published ? BlogStatus::Draft : BlogStatus::Published]);
        activity()->log('Blogs', 'Changed status to '.$blog->status->value, $blog);

        return back()->with('success', "“{$blog->blog_title}” is now {$blog->status->value}.");
    }

    public function duplicate(Blog $blog, SaveBlog $saveBlog): RedirectResponse
    {
        Gate::authorize('create', Blog::class);

        $copy = DB::transaction(function () use ($blog, $saveBlog) {
            $copy = $blog->replicate(['slug', 'views', 'status', 'featured_post']);
            $copy->blog_title = $blog->blog_title.' (Copy)';
            $copy->slug = $saveBlog->uniqueSlug($blog->slug.'-copy');
            $copy->status = BlogStatus::Draft;
            $copy->user_id = auth()->id();
            $copy->save();
            $copy->tags()->sync($blog->tags()->pluck('blog_tags.id'));

            return $copy;
        });

        activity()->log('Blogs', 'Duplicated blog', $copy, ['source' => $blog->id]);

        return redirect()->route('admin.blogs.edit', $copy)->with('success', 'Blog duplicated as a draft.');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        Gate::authorize('delete', $blog);

        activity()->log('Blogs', 'Deleted blog', $blog, ['title' => $blog->blog_title]);
        Storage::disk('public')->delete(array_filter([$blog->featured_image, $blog->og_image]));
        $blog->delete();

        return back()->with('success', 'Blog deleted.');
    }

    private function options(): array
    {
        return [
            'categories' => BlogCategory::active()->orderBy('category_name')->pluck('category_name', 'id'),
            'tags' => BlogTag::where('status', RecordStatus::Active)->orderBy('tag_name')->pluck('tag_name', 'id'),
        ];
    }
}
