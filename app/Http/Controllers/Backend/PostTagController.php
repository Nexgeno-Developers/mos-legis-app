<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PostTagController extends Controller
{
    protected $moduleName;
    protected $folderName;
    protected $routeName;

    public function __construct()
    {
        $this->moduleName = 'Post Tags';
        $this->folderName = 'post-tags';
        $this->routeName = 'post-tags';
        view()->share('moduleName', $this->moduleName);
        view()->share('folderName', $this->folderName);
        view()->share('routeName', $this->routeName);
    }

    public function index()
    {
        $search = request()->input('search');

        $query = Tag::query();

        if (auth()->user()?->company_id) {
            $query->where('company_id', auth()->user()->company_id);
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
        return view('backend.' . $this->folderName . '.create');
    }

    public function store(Request $request)
    {
        $companyId = $request->input('company_id') ?? auth()->user()?->company_id;
        $request->merge(['company_id' => $companyId]);

        $request->validate([
            'name' => 'required|string|min:2|max:200',
            'slug' => [
                'required',
                'string',
                'max:200',
                'regex:/^[A-Za-z0-9][A-Za-z0-9\\-_.\\/]*$/',
                Rule::unique('tags')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'company_id' => 'required|exists:companies,id',
        ]);

        $tag = new Tag();
        $tag->name = $request->input('name');
        $tag->slug = normalize_post_slug($request->input('slug'));
        $tag->company_id = $companyId;
        $tag->save();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['status' => true, 'notification' => 'Record created successfully!']);
        }

        return redirect()->route($this->routeName . '.index')->with('success', 'Record created successfully!');
    }

    public function edit(string $id)
    {
        $pageData = Tag::findOrFail($id);

        return view('backend.' . $this->folderName . '.edit', compact('pageData'));
    }

    public function update(Request $request, $id)
    {
        $tag = Tag::findOrFail($id);
        $companyId = $request->input('company_id') ?? $tag->company_id ?? auth()->user()?->company_id;
        $request->merge(['company_id' => $companyId]);

        $request->validate([
            'name' => 'required|string|min:2|max:200',
            'slug' => [
                'required',
                'string',
                'max:200',
                'regex:/^[A-Za-z0-9][A-Za-z0-9\\-_.\\/]*$/',
                Rule::unique('tags')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($id),
            ],
            'company_id' => 'required|exists:companies,id',
        ]);

        $tag->name = $request->input('name');
        $tag->slug = normalize_post_slug($request->input('slug'));
        $tag->company_id = $companyId;
        $tag->save();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['status' => true, 'notification' => 'Record updated successfully!']);
        }

        return redirect()->route($this->routeName . '.edit', $id)->with('success', 'Record updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $tag = Tag::withCount('posts')->findOrFail($id);

            if ($tag->posts_count > 0) {
                if (request()->ajax() || request()->expectsJson()) {
                    return response()->json(['status' => false, 'notification' => 'Cannot delete tag while it is assigned to posts.']);
                }
                return redirect()->route($this->routeName . '.index')->with('error', 'Cannot delete tag while it is assigned to posts.');
            }

            $tag->delete();

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
