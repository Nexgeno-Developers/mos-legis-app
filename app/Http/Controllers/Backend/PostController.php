<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Services\ApiPayloadCache;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    protected $moduleName;
    protected $folderName;
    protected $routeName;

    public function __construct()
    {
        $this->moduleName = 'Posts';
        $this->folderName = 'posts';
        $this->routeName = 'posts';
        view()->share('moduleName', $this->moduleName);
        view()->share('folderName', $this->folderName);
        view()->share('routeName', $this->routeName);
    }

    public function index()
    {
        $search = request()->input('search');
        $layout = request()->input('layout');
        $status = request()->input('status');

        $query = Post::with(['author', 'categories', 'tags']);

        if (auth()->user()?->company_id) {
            $query->where('company_id', auth()->user()->company_id);
        }

        if (!empty($layout) && array_key_exists($layout, getPostLayouts())) {
            $query->where('layout', $layout);
        }

        if ($status !== null && $status !== '') {
            $query->where('is_active', $status);
        }

        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        $query->orderBy('id', 'desc');

        $postData = $query->paginate(config('custom.pagination_per_page'));
        $layouts = getPostLayouts();

        return view('backend.' . $this->folderName . '.index', compact('postData', 'layouts'));
    }

    public function create()
    {
        $postData = new Post();
        $postData->layout = 'default_post_detail';
        $postData->setRelation('meta', collect());

        $authorsQuery = Author::where('is_active', 1)->orderBy('name');
        $categoriesQuery = Category::where('is_active', 1)->orderBy('name');
        $tagsQuery = Tag::orderBy('name');

        if (auth()->user()?->company_id) {
            $authorsQuery->where('company_id', auth()->user()->company_id);
            $categoriesQuery->where('company_id', auth()->user()->company_id);
            $tagsQuery->where('company_id', auth()->user()->company_id);
        }

        $authors = $authorsQuery->get();
        $categories = $categoriesQuery->get();
        $tags = $tagsQuery->get();

        return view('backend.' . $this->folderName . '.create', compact('postData', 'authors', 'categories', 'tags'));
    }

    public function store(Request $request)
    {
        $layout = 'default_post_detail';
        $companyId = $request->input('company_id') ?? auth()->user()?->company_id;

        $request->merge(['company_id' => $companyId]);
        $request->validate(post_validation_rules($layout, null, $companyId));

        try {
            $autoSlugEnabled = $request->boolean('auto_slug_enabled');
            $autoSlugs = $autoSlugEnabled
                ? $this->buildAutoSlugs($request->input('category_ids', []), $request->slug, $companyId)
                : [];
            $this->ensureAutoSlugsUnique($autoSlugs, $companyId, null);

            $post = Post::create([
                'title' => $request->title,
                'slug' => normalize_post_slug($request->slug),
                'language' => $request->language ?? 'en',
                'content' => $request->content,
                'featured_image' => $request->featured_image,
                'layout' => $layout,
                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'seo_keywords' => $request->seo_keywords,
                'seo_schema' => $request->seo_schema,
                'is_active' => $request->is_active,
                'company_id' => $companyId,
                'author_id' => $request->author_id,
                'published_at' => $request->published_at,
                'auto_slug' => $autoSlugEnabled && !empty($autoSlugs) ? $autoSlugs : null,
            ]);

            $post->categories()->sync($request->input('category_ids', []));
            $post->tags()->sync($request->input('tag_ids', []));

            post_sync_meta($post, $request->input('meta', []), getPostLayoutMetaKeys($layout));

            ApiPayloadCache::invalidatePost((int) $post->id, true);

            return redirect()->route($this->routeName . '.index')->with('success', 'Record created successfully!');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->route($this->routeName . '.create')->with('error', 'There was an error creating the record.');
        }
    }

    public function edit(string $id)
    {
        $postData = Post::with(['meta', 'categories', 'tags'])->findOrFail($id);
        $postData->layout = 'default_post_detail';

        $authorsQuery = Author::where('is_active', 1)->orderBy('name');
        $categoriesQuery = Category::where('is_active', 1)->orderBy('name');
        $tagsQuery = Tag::orderBy('name');

        if (auth()->user()?->company_id) {
            $authorsQuery->where('company_id', auth()->user()->company_id);
            $categoriesQuery->where('company_id', auth()->user()->company_id);
            $tagsQuery->where('company_id', auth()->user()->company_id);
        }

        $authors = $authorsQuery->get();
        $categories = $categoriesQuery->get();
        $tags = $tagsQuery->get();

        $selectedCategoryIds = $postData->categories->pluck('id')->toArray();
        $selectedTagIds = $postData->tags->pluck('id')->toArray();

        return view('backend.' . $this->folderName . '.edit', compact(
            'postData',
            'authors',
            'categories',
            'tags',
            'selectedCategoryIds',
            'selectedTagIds'
        ));
    }

    public function layoutFields(Request $request, $id = null)
    {
        $layout = 'default_post_detail';

        if ($id) {
            $postData = Post::with('meta')->findOrFail($id);
        } else {
            $postData = new Post();
            $postData->setRelation('meta', collect());
        }

        $postData->layout = $layout;

        return response()->view('backend.posts.edit-layouts.' . $layout, compact('postData'));
    }

    public function update(Request $request, $id)
    {
        $post = Post::with('meta')->findOrFail($id);
        $layout = 'default_post_detail';
        $companyId = $request->input('company_id') ?? $post->company_id ?? auth()->user()?->company_id;

        $request->merge(['company_id' => $companyId]);
        $request->validate(post_validation_rules($layout, (int) $id, $companyId));

        try {
            $autoSlugEnabled = $request->boolean('auto_slug_enabled');
            $autoSlugs = $autoSlugEnabled
                ? $this->buildAutoSlugs($request->input('category_ids', []), $request->slug, $companyId)
                : [];
            $this->ensureAutoSlugsUnique($autoSlugs, $companyId, (int) $id);

            $post->update([
                'title' => $request->title,
                'slug' => normalize_post_slug($request->slug),
                'language' => $request->language ?? 'en',
                'content' => $request->content,
                'featured_image' => $request->featured_image,
                'layout' => $layout,
                'seo_title' => $request->seo_title,
                'seo_description' => $request->seo_description,
                'seo_keywords' => $request->seo_keywords,
                'seo_schema' => $request->seo_schema,
                'is_active' => $request->is_active,
                'company_id' => $companyId,
                'author_id' => $request->author_id,
                'published_at' => $request->published_at,
                'auto_slug' => $autoSlugEnabled && !empty($autoSlugs) ? $autoSlugs : null,
            ]);

            $post->categories()->sync($request->input('category_ids', []));
            $post->tags()->sync($request->input('tag_ids', []));

            post_sync_meta($post, $request->input('meta', []), getPostLayoutMetaKeys($layout));

            ApiPayloadCache::invalidatePost((int) $post->id, true);

            return redirect()->route($this->routeName . '.edit', $id)->with('success', 'Record updated successfully');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            return redirect()->route($this->routeName . '.edit', $id)->with('error', 'There was an error updating the record.');
        }
    }

    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            ApiPayloadCache::invalidatePost((int) $post->id, true);
            $post->categories()->detach();
            $post->tags()->detach();
            $post->meta()->delete();
            $post->delete();

            return redirect()->route($this->routeName . '.index')->with('success', 'Record deleted successfully!');
        } catch (\Exception $e) {
            return redirect()->route($this->routeName . '.index')->with('error', 'There was an error deleting the record.');
        }
    }

    private function buildAutoSlugs(array $categoryIds, string $baseSlug, ?int $companyId): array
    {
        $baseSlug = normalize_post_slug($baseSlug);
        if (empty($baseSlug) || empty($categoryIds)) {
            return [];
        }

        $categoryQuery = Category::query()->whereIn('id', $categoryIds);
        if (!empty($companyId)) {
            $categoryQuery->where('company_id', $companyId);
        }

        $categorySlugs = $categoryQuery->pluck('slug')->toArray();

        $autoSlugs = [];
        foreach ($categorySlugs as $categorySlug) {
            $categorySlug = normalize_post_slug($categorySlug);
            if ($categorySlug === '') {
                continue;
            }
            $autoSlugs[] = $categorySlug . '/' . $baseSlug;
        }

        return array_values(array_unique($autoSlugs));
    }

    private function ensureAutoSlugsUnique(array $autoSlugs, ?int $companyId, ?int $ignorePostId): void
    {
        if (empty($autoSlugs)) {
            return;
        }

        $query = Post::query();
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }
        if (!empty($ignorePostId)) {
            $query->where('id', '!=', $ignorePostId);
        }

        $query->where(function ($q) use ($autoSlugs) {
            foreach ($autoSlugs as $slug) {
                $q->orWhere('slug', $slug)
                    ->orWhereJsonContains('auto_slug', $slug);
            }
        });

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'auto_slug_enabled' => 'Auto generated slug already exists for another post.',
            ]);
        }

        $pageQuery = Page::query()->whereIn('slug', $autoSlugs);
        if (!empty($companyId)) {
            $pageQuery->where('company_id', $companyId);
        }
        if ($pageQuery->exists()) {
            throw ValidationException::withMessages([
                'auto_slug_enabled' => 'Auto generated slug already exists in pages.',
            ]);
        }
    }
}
