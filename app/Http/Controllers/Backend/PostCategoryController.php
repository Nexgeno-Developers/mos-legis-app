<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ApiPayloadCache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostCategoryController extends Controller
{
    protected $moduleName;
    protected $folderName;
    protected $routeName;

    public function __construct()
    {
        $this->moduleName = 'Post Categories';
        $this->folderName = 'post-categories';
        $this->routeName = 'post-categories';
        view()->share('moduleName', $this->moduleName);
        view()->share('folderName', $this->folderName);
        view()->share('routeName', $this->routeName);
    }

    public function index()
    {
        $search = request()->input('search');
        $status = request()->input('status');

        $query = Category::with('parent');

        if (auth()->user()?->company_id) {
            $query->where('company_id', auth()->user()->company_id);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy('id', 'desc');

        $pageData = $query->paginate(config('custom.pagination_per_page'));

        return view('backend.' . $this->folderName . '.index', compact('pageData'));
    }

    public function create()
    {
        $parentCategoriesQuery = Category::orderBy('name');
        if (auth()->user()?->company_id) {
            $parentCategoriesQuery->where('company_id', auth()->user()->company_id);
        }
        $parentCategories = $parentCategoriesQuery->get();

        return view('backend.' . $this->folderName . '.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $companyId = $request->input('company_id') ?? auth()->user()?->company_id;
        $request->merge(['company_id' => $companyId]);

        $request->validate([
            'name' => 'required|string|min:3|max:200',
            'slug' => [
                'required',
                'string',
                'max:200',
                'regex:/^[A-Za-z0-9][A-Za-z0-9\\-_.\\/]*$/',
                Rule::unique('categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'breadcrumb_image' => 'nullable|string',
            'description' => 'nullable|string|max:2000',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'required|boolean',
            'company_id' => 'required|exists:companies,id',
        ]);

        $category = new Category();
        $category->name = $request->input('name');
        $category->slug = normalize_post_slug($request->input('slug'));
        $category->breadcrumb_image = $request->input('breadcrumb_image');
        $category->description = $request->input('description');
        $category->parent_id = $request->input('parent_id') ?: null;
        $category->is_active = $request->input('is_active');
        $category->company_id = $companyId;
        $category->save();

        ApiPayloadCache::invalidateCategory((int) $category->id, true, true, true);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['status' => true, 'notification' => 'Record created successfully!']);
        }

        return redirect()->route($this->routeName . '.index')->with('success', 'Record created successfully!');
    }

    public function edit(string $id)
    {
        $pageData = Category::findOrFail($id);
        $parentCategoriesQuery = Category::where('id', '!=', $id)->orderBy('name');
        if (auth()->user()?->company_id) {
            $parentCategoriesQuery->where('company_id', auth()->user()->company_id);
        }
        $parentCategories = $parentCategoriesQuery->get();

        return view('backend.' . $this->folderName . '.edit', compact('pageData', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $companyId = $request->input('company_id') ?? $category->company_id ?? auth()->user()?->company_id;

        $request->merge(['company_id' => $companyId]);

        $request->validate([
            'name' => 'required|string|min:3|max:200',
            'slug' => [
                'required',
                'string',
                'max:200',
                'regex:/^[A-Za-z0-9][A-Za-z0-9\\-_.\\/]*$/',
                Rule::unique('categories')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($id),
            ],
            'breadcrumb_image' => 'nullable|string',
            'description' => 'nullable|string|max:2000',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'required|boolean',
            'company_id' => 'required|exists:companies,id',
        ]);

        $category->name = $request->input('name');
        $category->slug = normalize_post_slug($request->input('slug'));
        $category->breadcrumb_image = $request->input('breadcrumb_image');
        $category->description = $request->input('description');
        $category->parent_id = $request->input('parent_id') ?: null;
        $category->is_active = $request->input('is_active');
        $category->company_id = $companyId;
        $category->save();

        ApiPayloadCache::invalidateCategory((int) $category->id, true, true, true);

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['status' => true, 'notification' => 'Record updated successfully!']);
        }

        return redirect()->route($this->routeName . '.edit', $id)->with('success', 'Record updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $category = Category::withCount(['posts', 'children'])->findOrFail($id);

            if ($category->posts_count > 0) {
                if (request()->ajax() || request()->expectsJson()) {
                    return response()->json(['status' => false, 'notification' => 'Cannot delete category while it is assigned to posts.']);
                }
                return redirect()->route($this->routeName . '.index')->with('error', 'Cannot delete category while it is assigned to posts.');
            }

            if ($category->children_count > 0) {
                if (request()->ajax() || request()->expectsJson()) {
                    return response()->json(['status' => false, 'notification' => 'Cannot delete category that has child categories.']);
                }
                return redirect()->route($this->routeName . '.index')->with('error', 'Cannot delete category that has child categories.');
            }

            $category->delete();

            ApiPayloadCache::invalidateCategory((int) $id, true, true, true);

            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['status' => true, 'notification' => 'Record deleted successfully!']);
            }

            return redirect()->route($this->routeName . '.index')->with('success', 'Record deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->expectsJson()) {
                return response()->json(['status' => false, 'notification' => 'There was an error deleting the record.']);
            }

            return redirect()->route($this->routeName . '.index')->with('error', 'There was an error deleting the record.');
        }
    }
}
