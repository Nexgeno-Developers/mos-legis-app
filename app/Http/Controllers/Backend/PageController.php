<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use App\Models\PageMeta;
use App\Models\Gallery;
use App\Services\ApiPayloadCache;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    protected $moduleName;
    protected $folderName;
    protected $routeName;

    public function __construct()
    {
        $this->moduleName = 'Pages';
        $this->folderName = 'pages';
        $this->routeName = 'pages';
        view()->share('moduleName', $this->moduleName);
        view()->share('folderName', $this->folderName);
        view()->share('routeName', $this->routeName);
    } 

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Get the search parameter from the request
        $search = request()->input('search');
        $layout = request()->input('layout');  
        
        // Read and sanitize sort inputs
        $sort = request()->input('sort', 'id');
        $direction = strtolower(request()->input('direction', 'asc')) === 'asc' ? 'asc' : 'desc';        
        $direction = "desc";
        // Start building the query
        $query = Page::with('meta');
    
        // Filter by authenticated user's company_id if available
        if (auth()->user()->company_id) {
            $query->where('company_id', auth()->user()->company_id);
        }

        if (!empty($layout)) {
            // Validate layout against known layouts to avoid arbitrary filtering
            if (array_key_exists($layout, getPageLayouts())) {
                $query->where('layout', $layout);
            }
        }
    
        if ($search) {
            $query->where(function($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('slug', 'like', '%'.$search.'%');
            });
        }      

        //$query->orderBy('id', 'desc');
        // Apply ordering from request
        $query->orderBy($sort, $direction);
    
        $pageData = $query->paginate(config('custom.pagination_per_page'));
    
        // Return the view with data
        return view('backend.' . $this->folderName . '.index', compact('pageData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.' . $this->folderName . '.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate form data
        $request->validate([
            'title' => 'required|string|min:3|max:255',
            //'slug' => 'required|string|unique:pages,slug|max:255',
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                }),
                function ($attribute, $value, $fail) use ($request) {
                    if (slug_conflicts_with_pages_or_posts($value, $request->company_id, null, null)) {
                        $fail('Slug already exists in pages or posts.');
                    }
                },
            ],            
            'content' => 'required|string', 
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_schema' => 'nullable|string',
            'is_active' => 'required|boolean',
            'company_id' => 'required|exists:companies,id',
        ]);
    
        try {
            $team = Page::create([
                'title' => $request->title,
                'slug' => strtolower(str_replace(' ', '-', $request->slug)),
                'content' => $request->content,
                'layout' => $request->layout ?? 'default',
                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'seo_schema' => $request->seo_schema,
                'is_active' => $request->is_active,
                'company_id' => $request->company_id,
            ]);

            ApiPayloadCache::invalidatePage((int) $team->id, true);
    
            // Return success response
            return redirect()->route($this->routeName . '.index')->with('success', 'Record created successfully!');
    
        } catch (\Exception $e) {
            // Return error response
            return redirect()->route($this->routeName . '.create')->with('error', 'There was an error creating the record.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pageData = Page::findOrFail($id);
        return view('backend.' . $this->folderName . '.edit', compact('pageData'));
    }

    /**
     * Return the layout-specific fields HTML for the edit form.
     */
    public function layoutFields(Request $request, $id)
    {
        $pageData = Page::findOrFail($id);
        $layout = $request->get('layout', $pageData->layout);

        // Ensure layout is one of the allowed layouts to avoid arbitrary view loading
        if (!array_key_exists($layout, getPageLayouts())) {
            abort(400, 'Invalid layout');
        }

        $pageData->layout = $layout;

        return response()->view('backend.pages.edit-layouts.' . $layout, compact('pageData'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Validate form data
        $request->validate([
            'title' => 'required|string|min:3|max:255',
            //'slug' => 'required|string|max:255|unique:pages,slug,' . $id, // Ignore current record slug for uniqueness
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pages')->where(function ($query) use ($request) {
                    return $query->where('company_id', $request->company_id);
                })->ignore($id), // Ignore the current record
                function ($attribute, $value, $fail) use ($request, $id) {
                    if (slug_conflicts_with_pages_or_posts($value, $request->company_id, (int) $id, null)) {
                        $fail('Slug already exists in pages or posts.');
                    }
                },
            ],            
            'content' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'seo_schema' => 'nullable|string',
            'is_active' => 'required|boolean',
            'company_id' => 'required|exists:companies,id',
        ]);
    
        try {
            // Find the record and update
            $record = Page::findOrFail($id);
            $record->update([
                'title' => $request->title,
                'slug' => strtolower(str_replace(' ', '-', $request->slug)),
                'content' => $request->content,
                'layout' => $request->layout,
                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'seo_schema' => $request->seo_schema,
                'is_active' => $request->is_active,
                'company_id' => $request->company_id,
            ]);

            // Handle meta fields
            $metaFields = $request->input('meta', []); // Get all meta fields from the request

            // Get existing meta keys for this record
            $existingMetaKeys = $record->meta()->pluck('meta_key')->toArray();

            // Loop existing meta keys to check if they are missing in submitted request
            foreach ($existingMetaKeys as $existingKey) {
                // If submitted meta does not have this key, delete it
                if (!array_key_exists($existingKey, $metaFields)) {
                    $record->meta()->where('meta_key', $existingKey)->delete();
                }
            }            

            foreach ($metaFields as $key => $value) {
                // Check if the meta key exists for the current page
                $existingMeta = $record->meta()->where('meta_key', $key)->first();
                
                //encode if value is in array
                $value = is_array($value) ? json_encode($value) : $value;
            
                if ($existingMeta) {
                    // If the meta key exists, update it regardless of $value being empty
                    $existingMeta->update(['meta_value' => $value]);
                } else {
                    // If the meta key does not exist, create a new record only if $value is not empty
                    if (!empty($value)) {
                        $record->meta()->create([
                            'meta_key' => $key,
                            'meta_value' => $value
                        ]);
                    }
                }
            }              

            ApiPayloadCache::invalidatePage((int) $id, true);
    
            return redirect()->route($this->routeName . '.edit', $id)->with('success', 'Record updated successfully');
        } catch (\Exception $e) {
            return redirect()->route($this->routeName . '.edit', $id)->with('error', 'There was an error updating the record.');
        }
    }    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            // Attempt to delete the record
            $page = Page::findOrFail($id);
            ApiPayloadCache::invalidatePage((int) $page->id, true);
            $page->meta()->delete();
            $page->delete();

            // Redirect back with a success message
            return redirect()->route($this->routeName . '.index')->with('success', 'Record deleted successfully!');
        } catch (\Exception $e) {
            // Redirect back with an error message
            return redirect()->route($this->routeName . 'index')->with('error', 'There was an error deleting the record.');
        }
    }

    /**
     * Clone the specified resource.
     */
    public function clone($id)
    {
        try {
            // Find the original page
            $originalPage = Page::findOrFail($id);

            // Create a new page with modified title and slug
            $clonedPage = Page::create([
                'title' => 'Copy ' . $originalPage->title,
                'slug' => 'copy-' . $originalPage->slug . '-' . rand(10000, 99999),
                'content' => $originalPage->content,
                'layout' => $originalPage->layout,
                'seo_title' => $originalPage->seo_title,
                'seo_description' => $originalPage->seo_description,
                'seo_schema' => $originalPage->seo_schema,
                'is_active' => $originalPage->is_active,
                'company_id' => $originalPage->company_id,
            ]);

            // Clone all meta fields
            foreach ($originalPage->meta as $meta) {
                $clonedPage->meta()->create([
                    'meta_key' => $meta->meta_key,
                    'meta_value' => $meta->meta_value,
                ]);
            }

            ApiPayloadCache::invalidatePage((int) $clonedPage->id, true);

            // Redirect to the edit page of the cloned page
            return redirect()->route($this->routeName . '.index')->with('success', 'Page cloned successfully!');
        } catch (\Exception $e) {
            // Redirect back with an error message
            return redirect()->route($this->routeName . '.index')->with('error', 'There was an error cloning the page.');
        }
    }
}
