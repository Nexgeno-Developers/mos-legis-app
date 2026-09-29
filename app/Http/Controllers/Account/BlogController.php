<?php

namespace App\Http\Controllers\Account;

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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * SOW B.05 — authors post and manage their own blogs (approval per Settings).
 */
class BlogController extends Controller
{
    public function index(Request $request): View
    {
        return view('account.blogs.index', [
            'blogs' => $request->user()->blogs()->with('category:id,category_name')->latest('id')->paginate(10),
        ]);
    }

    public function create(Request $request): View
    {
        return view('account.blogs.form', ['blog' => new Blog(['publish_date' => today(), 'status' => BlogStatus::Draft])] + $this->options());
    }

    public function store(BlogRequest $request, SaveBlog $saveBlog): RedirectResponse
    {
        $blog = $saveBlog->handle($request->validated(), $request->user(), asAuthor: true);

        return redirect()->route('account.blogs.index')->with('success', $this->message($blog));
    }

    public function edit(Blog $blog): View
    {
        Gate::authorize('update', $blog);

        return view('account.blogs.form', ['blog' => $blog->load('tags:id')] + $this->options());
    }

    public function update(BlogRequest $request, Blog $blog, SaveBlog $saveBlog): RedirectResponse
    {
        $saveBlog->handle($request->validated(), $request->user(), $blog, asAuthor: true);

        return redirect()->route('account.blogs.index')->with('success', $this->message($blog->fresh()));
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        Gate::authorize('delete', $blog);

        Storage::disk('public')->delete(array_filter([$blog->featured_image, $blog->og_image]));
        $blog->delete();

        return back()->with('success', 'Blog deleted.');
    }

    private function message(Blog $blog): string
    {
        return match ($blog->status) {
            BlogStatus::Pending => 'Blog saved and sent to the editors for approval.',
            BlogStatus::Published => 'Blog published.',
            default => 'Draft saved.',
        };
    }

    private function options(): array
    {
        return [
            'categories' => BlogCategory::active()->orderBy('category_name')->pluck('category_name', 'id'),
            'tags' => BlogTag::where('status', RecordStatus::Active)->orderBy('tag_name')->pluck('tag_name', 'id'),
        ];
    }
}
