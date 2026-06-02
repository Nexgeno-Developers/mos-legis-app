<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = 'Blogs';
        view()->share('moduleName', $this->moduleName);
    }

    public function index()
    {
        $categoryId = request()->input('category');
        $search = request()->input('search');
        $status = request()->input('status');

        $query = Blog::with('categories');

        if ($categoryId) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('blog_categories.id', $categoryId);
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('excerpt', 'like', '%'.$search.'%');
            });
        }

        $query->orderBy('id', 'desc');

        $pageData = $query->paginate(config('custom.pagination_per_page'));

        $categoryList = BlogCategory::where('is_active', 1)->orderBy('name', 'asc')->get();

        return view('backend.blogs.index', compact('pageData', 'categoryList'));
    }

    public function create()
    {
        $categoryList = BlogCategory::where('is_active', 1)->orderBy('name', 'asc')->get();
        return view('backend.blogs.create', compact('categoryList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|min:3|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blogs', 'slug')->where(function ($query) {
                    return $query->where('company_id', auth()->user()->company_id);
                }),
            ],
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
            'is_active' => 'required|boolean',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:blog_categories,id',
        ]);

        $blog = new Blog();
        $blog->title = $request->input('title');
        $blog->slug = $request->input('slug');
        $blog->excerpt = $request->input('excerpt');
        $blog->content = $request->input('content');
        $blog->image = $request->input('image');
        $blog->seo_title = $request->input('seo_title');
        $blog->seo_description = $request->input('seo_description');
        $blog->published_at = $request->input('published_at');
        $blog->is_active = $request->input('is_active');
        $blog->company_id = auth()->user()->company_id;
        $blog->save();

        $blog->categories()->sync($request->input('category_ids', []));

        return response()->json(['status' => true, 'notification' => 'Record created successfully!']);
    }

    public function edit(string $id)
    {
        $pageData = Blog::with('categories')->findOrFail($id);

        $selectedCategoryIds = $pageData->categories()->pluck('blog_categories.id')->toArray();

        $categoryList = BlogCategory::where('is_active', 1)
            ->orWhereIn('id', $selectedCategoryIds)
            ->orderBy('name', 'asc')
            ->get();

        return view('backend.blogs.edit', compact('pageData', 'categoryList', 'selectedCategoryIds'));
    }

    public function update(Request $request, $id)
    {
        $blog = Blog::findOrFail($id);

        $request->validate([
            'title' => 'required|string|min:3|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blogs', 'slug')
                    ->ignore($blog->id)
                    ->where(function ($query) {
                        return $query->where('company_id', auth()->user()->company_id);
                    }),
            ],
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'published_at' => 'nullable|date',
            'is_active' => 'required|boolean',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:blog_categories,id',
        ]);

        $blog->title = $request->input('title');
        $blog->slug = $request->input('slug');
        $blog->excerpt = $request->input('excerpt');
        $blog->content = $request->input('content');
        $blog->image = $request->input('image');
        $blog->seo_title = $request->input('seo_title');
        $blog->seo_description = $request->input('seo_description');
        $blog->published_at = $request->input('published_at');
        $blog->is_active = $request->input('is_active');
        $blog->save();

        $blog->categories()->sync($request->input('category_ids', []));

        return response()->json(['status' => true, 'notification' => 'Record updated successfully!']);
    }

    public function destroy($id)
    {
        try {
            Blog::destroy($id);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['status' => true, 'notification' => 'Record deleted successfully!']);
            }

            return redirect()->route('blogs.index')->with('success', 'Record deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Error deleting Blog record', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'blog_id' => $id,
            ]);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['status' => false, 'notification' => 'There was an error deleting the record.']);
            }

            return redirect()->route('blogs.index')->with('error', 'There was an error deleting the record.');
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = explode(',', $request->input('ids'));

            if (empty($ids) || !is_array($ids)) {
                return response()->json(['status' => false, 'notification' => 'No items selected for deletion.']);
            }

            $deleted = Blog::whereIn('id', $ids)->delete();

            if ($deleted > 0) {
                return response()->json([
                    'status' => true,
                    'notification' => $deleted.' record(s) deleted successfully!',
                ]);
            }

            return response()->json(['status' => false, 'notification' => 'No records were deleted.']);
        } catch (\Exception $e) {
            \Log::error('Error bulk deleting Blog records', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'ids' => $request->input('ids'),
            ]);

            return response()->json(['status' => false, 'notification' => 'There was an error deleting the records.']);
        }
    }

    public function bulkActive(Request $request)
    {
        try {
            $ids = explode(',', $request->input('ids'));

            if (empty($ids) || !is_array($ids)) {
                return response()->json(['status' => false, 'notification' => 'No items selected for activation.']);
            }

            $updated = Blog::whereIn('id', $ids)->update(['is_active' => 1]);

            if ($updated > 0) {
                return response()->json([
                    'status' => true,
                    'notification' => $updated.' record(s) activated successfully!',
                ]);
            }

            return response()->json(['status' => false, 'notification' => 'No records were activated.']);
        } catch (\Exception $e) {
            \Log::error('Error bulk activating Blog records', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'ids' => $request->input('ids'),
            ]);

            return response()->json(['status' => false, 'notification' => 'There was an error activating the records.']);
        }
    }

    public function bulkInactive(Request $request)
    {
        try {
            $ids = explode(',', $request->input('ids'));

            if (empty($ids) || !is_array($ids)) {
                return response()->json(['status' => false, 'notification' => 'No items selected for deactivation.']);
            }

            $updated = Blog::whereIn('id', $ids)->update(['is_active' => 0]);

            if ($updated > 0) {
                return response()->json([
                    'status' => true,
                    'notification' => $updated.' record(s) deactivated successfully!',
                ]);
            }

            return response()->json(['status' => false, 'notification' => 'No records were deactivated.']);
        } catch (\Exception $e) {
            \Log::error('Error bulk deactivating Blog records', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'ids' => $request->input('ids'),
            ]);

            return response()->json(['status' => false, 'notification' => 'There was an error deactivating the records.']);
        }
    }
}
