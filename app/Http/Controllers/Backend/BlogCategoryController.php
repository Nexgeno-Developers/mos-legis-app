<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = 'Blog Categories';
        view()->share('moduleName', $this->moduleName);
    }

    public function index()
    {
        $search = request()->input('search');
        $status = request()->input('status');

        $query = BlogCategory::query();

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        $query->orderBy('id', 'desc');

        $pageData = $query->paginate(config('custom.pagination_per_page'));

        return view('backend.blog-categories.index', compact('pageData'));
    }

    public function create()
    {
        return view('backend.blog-categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:200',
            'slug' => 'required|string|max:200',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ]);

        $category = new BlogCategory();
        $category->name = $request->input('name');
        $category->slug = $request->input('slug');
        $category->description = $request->input('description');
        $category->meta_title = $request->input('meta_title');
        $category->meta_description = $request->input('meta_description');
        $category->is_active = $request->input('is_active');
        $category->company_id = auth()->user()->company_id;
        $category->save();

        return response()->json(['status' => true, 'notification' => 'Record created successfully!']);
    }

    public function edit(string $id)
    {
        $pageData = BlogCategory::findOrFail($id);
        return view('backend.blog-categories.edit', compact('pageData'));
    }

    public function update(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|min:3|max:200',
            'slug' => 'required|string|max:200',
            'description' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'is_active' => 'required|boolean',
        ]);

        $category->name = $request->input('name');
        $category->slug = $request->input('slug');
        $category->description = $request->input('description');
        $category->meta_title = $request->input('meta_title');
        $category->meta_description = $request->input('meta_description');
        $category->is_active = $request->input('is_active');
        $category->save();

        return response()->json(['status' => true, 'notification' => 'Record updated successfully!']);
    }

    public function destroy($id)
    {
        try {
            BlogCategory::destroy($id);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['status' => true, 'notification' => 'Record deleted successfully!']);
            }

            return redirect()->route('blog-categories.index')->with('success', 'Record deleted successfully!');
        } catch (\Exception $e) {
            \Log::error('Error deleting BlogCategory record', [
                'error_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString(),
                'blog_category_id' => $id,
            ]);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['status' => false, 'notification' => 'There was an error deleting the record.']);
            }

            return redirect()->route('blog-categories.index')->with('error', 'There was an error deleting the record.');
        }
    }
}
