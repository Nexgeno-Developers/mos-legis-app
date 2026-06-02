<?php

namespace App\Http\Controllers\Backend;

use App\Models\SeoMeta;
use App\Services\ApiPayloadCache;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class SeoMetaController extends BaseController
{
    protected $module;

    public function __construct()
    {
        $this->module = 'seo-meta';
        view()->share('module', $this->module);

        $this->middleware('permission:seo-meta view')->only(['index', 'show']);
        $this->middleware('permission:seo-meta create')->only(['create', 'store', 'clone']);
        $this->middleware('permission:seo-meta edit')->only(['edit', 'update']);
        $this->middleware('permission:seo-meta delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request()->input('search');

        $query = SeoMeta::when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', '%' . $search . '%')
                    ->orWhere('meta_title', 'like', '%' . $search . '%')
                    ->orWhere('canonical_url', 'like', '%' . $search . '%');
            });
        })->orderBy('id', 'desc');

        $pageData = $query->paginate(10);

        return view('backend.' . $this->module . '.index', compact('pageData'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.' . $this->module . '.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'slug' => 'required|string|max:255|unique:seo_meta,slug',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'canonical_url' => 'nullable|string|max:255',
            'robots_index' => 'nullable|in:index,noindex',
            'robots_follow' => 'nullable|in:follow,nofollow',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|integer|exists:uploads,id',
            'twitter_title' => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string',
            'twitter_image' => 'nullable|integer|exists:uploads,id',
            'schema_json' => 'nullable',
            'sitemap_priority' => 'nullable|numeric|between:0,1',
        ]);

        try {
            SeoMeta::create([
                'slug' => $request->slug,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'meta_keywords' => $request->meta_keywords,
                'canonical_url' => $request->canonical_url,
                'robots_index' => $request->robots_index ?? 'index',
                'robots_follow' => $request->robots_follow ?? 'follow',
                'og_title' => $request->og_title,
                'og_description' => $request->og_description,
                'og_image' => $request->og_image,
                'twitter_title' => $request->twitter_title,
                'twitter_description' => $request->twitter_description,
                'twitter_image' => $request->twitter_image,
                'schema_json' => $request->schema_json,
                'sitemap_priority' => $request->sitemap_priority,
            ]);

            ApiPayloadCache::invalidatePageBySlug($request->slug);

            return response()->json(['status' => true, 'notification' => __('messages.created')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
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
     * Show the form for cloning an existing record (same fields as edit; saves as a new row).
     */
    public function clone($id)
    {
        $seoMeta = SeoMeta::findOrFail($id);

        return view('backend.' . $this->module . '.clone', compact('seoMeta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $seoMeta = SeoMeta::findOrFail($id);

        return view('backend.' . $this->module . '.edit', compact('seoMeta'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'slug' => 'required|string|max:255|unique:seo_meta,slug,' . $id,
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'canonical_url' => 'nullable|string|max:255',
            'robots_index' => 'nullable|in:index,noindex',
            'robots_follow' => 'nullable|in:follow,nofollow',
            'og_title' => 'nullable|string|max:255',
            'og_description' => 'nullable|string',
            'og_image' => 'nullable|integer|exists:uploads,id',
            'twitter_title' => 'nullable|string|max:255',
            'twitter_description' => 'nullable|string',
            'twitter_image' => 'nullable|integer|exists:uploads,id',
            'schema_json' => 'nullable',
            'sitemap_priority' => 'nullable|numeric|between:0,1',
        ]);

        try {
            $seoMeta = SeoMeta::findOrFail($id);
            $previousSlug = $seoMeta->slug;
            $seoMeta->slug = $request->slug;
            $seoMeta->meta_title = $request->meta_title;
            $seoMeta->meta_description = $request->meta_description;
            $seoMeta->meta_keywords = $request->meta_keywords;
            $seoMeta->canonical_url = $request->canonical_url;
            $seoMeta->robots_index = $request->robots_index ?? 'index';
            $seoMeta->robots_follow = $request->robots_follow ?? 'follow';
            $seoMeta->og_title = $request->og_title;
            $seoMeta->og_description = $request->og_description;
            $seoMeta->og_image = $request->og_image;
            $seoMeta->twitter_title = $request->twitter_title;
            $seoMeta->twitter_description = $request->twitter_description;
            $seoMeta->twitter_image = $request->twitter_image;
            $seoMeta->schema_json = $request->schema_json;
            $seoMeta->sitemap_priority = $request->sitemap_priority;
            $seoMeta->save();

            ApiPayloadCache::invalidatePageBySlug($previousSlug);
            if ($seoMeta->slug !== $previousSlug) {
                ApiPayloadCache::invalidatePageBySlug($seoMeta->slug);
            }

            return response()->json(['status' => true, 'notification' => __('messages.updated')]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'notification' => __('messages.failed')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $seoMeta = SeoMeta::findOrFail($id);
            $slug = $seoMeta->slug;
            $seoMeta->delete();

            ApiPayloadCache::invalidatePageBySlug($slug);

            return redirect()->route($this->module . '.index')->with('success', __('messages.deleted'));
        } catch (\Exception $e) {
            return redirect()->route($this->module . '.index')->with('error', __('messages.failed'));
        }
    }
}
