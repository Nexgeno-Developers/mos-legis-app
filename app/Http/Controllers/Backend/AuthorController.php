<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    protected $moduleName;
    protected $folderName;
    protected $routeName;

    public function __construct()
    {
        $this->moduleName = 'Authors';
        $this->folderName = 'authors';
        $this->routeName = 'authors';
        view()->share('moduleName', $this->moduleName);
        view()->share('folderName', $this->folderName);
        view()->share('routeName', $this->routeName);
    }

    public function index()
    {
        $search = request()->input('search');
        $status = request()->input('status');

        $query = Author::query();

        if (auth()->user()?->company_id) {
            $query->where('company_id', auth()->user()->company_id);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
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
            'name' => 'required|string|min:3|max:200',
            'email' => 'nullable|email|max:255',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|string',
            'is_active' => 'required|boolean',
            'company_id' => 'required|exists:companies,id',
        ]);

        $author = new Author();
        $author->name = $request->input('name');
        $author->slug = normalize_post_slug($request->input('name'));
        $author->email = $request->input('email');
        $author->bio = $request->input('bio');
        $author->profile_image = $request->input('profile_image');
        $author->is_active = $request->input('is_active');
        $author->company_id = $companyId;
        $author->save();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['status' => true, 'notification' => 'Record created successfully!']);
        }

        return redirect()->route($this->routeName . '.index')->with('success', 'Record created successfully!');
    }

    public function edit(string $id)
    {
        $pageData = Author::findOrFail($id);

        return view('backend.' . $this->folderName . '.edit', compact('pageData'));
    }

    public function update(Request $request, $id)
    {
        $author = Author::findOrFail($id);
        $companyId = $request->input('company_id') ?? $author->company_id ?? auth()->user()?->company_id;
        $request->merge(['company_id' => $companyId]);

        $request->validate([
            'name' => 'required|string|min:3|max:200',
            'email' => 'nullable|email|max:255',
            'bio' => 'nullable|string',
            'profile_image' => 'nullable|string',
            'is_active' => 'required|boolean',
            'company_id' => 'required|exists:companies,id',
        ]);

        $author->name = $request->input('name');
        $author->slug = normalize_post_slug($request->input('name'));
        $author->email = $request->input('email');
        $author->bio = $request->input('bio');
        $author->profile_image = $request->input('profile_image');
        $author->is_active = $request->input('is_active');
        $author->company_id = $companyId;
        $author->save();

        if (request()->ajax() || request()->expectsJson()) {
            return response()->json(['status' => true, 'notification' => 'Record updated successfully!']);
        }

        return redirect()->route($this->routeName . '.edit', $id)->with('success', 'Record updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $author = Author::withCount('posts')->findOrFail($id);

            if ($author->posts_count > 0) {
                $reassignTo = request()->input('reassign_author_id');

                if ($reassignTo && (int) $reassignTo !== (int) $author->id && Author::where('id', $reassignTo)->exists()) {
                    $author->posts()->update(['author_id' => $reassignTo]);
                } else {
                    if (request()->ajax() || request()->expectsJson()) {
                        return response()->json(['status' => false, 'notification' => 'Cannot delete author while assigned to posts.']);
                    }
                    return redirect()->route($this->routeName . '.index')->with('error', 'Cannot delete author while assigned to posts.');
                }
            }

            $author->delete();

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
