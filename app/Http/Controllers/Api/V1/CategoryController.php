<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->where('is_active', true);

        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        $categories = $query->orderBy('name')->get();

        return response()->json([
            'data' => $categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'breadcrumb_image' => filled($category->breadcrumb_image)
                    ? uploaded_asset_details_from_ids($category->breadcrumb_image)
                    : null,
                'parent_id' => $category->parent_id,
                'company_id' => $category->company_id,
            ])->values()->all(),
        ]);
    }

    public function show(string $slugOrId, Request $request): JsonResponse
    {
        $decoded = trim(rawurldecode($slugOrId), '/');
        $query = Category::query()->where('is_active', true);

        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        if (ctype_digit($decoded)) {
            $query->where('id', (int) $decoded);
        } else {
            $query->where('slug', normalize_post_slug($decoded));
        }

        $category = $query->first();

        if (!$category) {
            return response()->json([
                'error' => [
                    'message' => 'Category not found',
                    'code' => 'CATEGORY_NOT_FOUND',
                ],
            ], 404);
        }

        $children = $this->buildChildrenTree($category, $companyId);

        $parent = $category->parent_id ? Category::query()
            ->when(!empty($companyId), function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->where('id', $category->parent_id)
            ->first() : null;

        $parentPayload = $parent ? $this->categoryPayload($parent, $companyId, true) : null;

        $perPage = max(1, min((int) $request->get('per_page', 9), 50));

        return response()->json([
            'category' => array_merge(
                $this->categoryPayload($category, $companyId, false),
                [
                    // 'parent' => $parent ? $this->categoryPayload($parent, $companyId, false) : null,
                    // 'children' => $children,
                ]
            ),
            'layout' => $category->layout ?? 'default_category_listing',
            'parent' => $parentPayload,
            'children' => $children,
            'posts' => $this->postsForCategory($category, $companyId, $perPage),
        ]);
    }

    public function postsBySlug(string $slug, Request $request): JsonResponse
    {
        $normalizedSlug = normalize_post_slug(rawurldecode($slug));
        $categoryQuery = Category::query()->where('slug', $normalizedSlug)->where('is_active', true);
        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $categoryQuery->where('company_id', $companyId);
        }

        $category = $categoryQuery->first();

        if (!$category) {
            return response()->json([
                'error' => [
                    'message' => 'Category not found',
                    'code' => 'CATEGORY_NOT_FOUND',
                ],
            ], 404);
        }

        $postsQuery = $category->posts()
            ->where('is_active', true)
            ->with(['author', 'categories', 'tags', 'meta'])
            ->orderByDesc('published_at');

        if (!empty($companyId)) {
            $postsQuery->where('company_id', $companyId);
        }

        $perPage = max(1, min((int) $request->get('per_page', 9), 50));
        $posts = $postsQuery->paginate($perPage);

        return response()->json([
            'data' => $posts->getCollection()->map(fn ($post) => [
                'id' => $post->id,
                'slug' => $post->slug,
                'title' => $post->title,
                'layout' => $post->layout,
                'featured_image' => filled($post->featured_image)
                    ? uploaded_asset_details_from_ids($post->featured_image)
                    : null,
                'summary' => $this->extractSummaryFromMeta($post),
                'date' => $this->extractMetaValue($post, 'date'),
                'time' => $this->extractMetaValue($post, 'time'),
                'author' => $post->author ? [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                ] : null,
                'categories' => $post->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ])->values()->all(),
                'tags' => $post->tags->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])->values()->all(),
                'published_at' => $post->published_at?->toISOString(),
            ])->values()->all(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ],
            'category' => $this->categoryPayload($category, $companyId, true),
        ]);
    }

    private function categoryPayload(Category $category, ?int $companyId, bool $withChildren): array
    {
        $payload = [
            'id' => $category->id,
            'name' => $category->name,
            'title' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'breadcrumb_image' => filled($category->breadcrumb_image)
                ? uploaded_asset_details_from_ids($category->breadcrumb_image)
                : null,
            // 'parent_id' => $category->parent_id,
            // 'company_id' => $category->company_id,
        ];

        if ($withChildren) {
            $payload['children'] = $this->buildChildrenTree($category, $companyId);
        }

        return $payload;
    }

    private function buildChildrenTree(Category $category, ?int $companyId = null): array
    {
        $childrenQuery = $category->children()->where('is_active', true);
        if (!empty($companyId)) {
            $childrenQuery->where('company_id', $companyId);
        }

        $children = $childrenQuery->orderBy('name')->get();

        return $children->map(function (Category $child) use ($companyId) {
            return [
                'id' => $child->id,
                'name' => $child->name,
                'title' => $child->name,
                'slug' => $child->slug,
                'description' => $child->description,
                'breadcrumb_image' => filled($child->breadcrumb_image)
                    ? uploaded_asset_details_from_ids($child->breadcrumb_image)
                    : null,
                'parent_id' => $child->parent_id,
                // 'company_id' => $child->company_id,
                // 'children' => $this->buildChildrenTree($child, $companyId),
            ];
        })->values()->all();
    }

    private function postsForCategory(Category $category, ?int $companyId, int $perPage): array
    {
        $postsQuery = $category->posts()
            ->where('is_active', true)
            ->with('meta')
            ->orderByDesc('published_at');

        if (!empty($companyId)) {
            $postsQuery->where('company_id', $companyId);
        }

        $posts = $postsQuery->paginate($perPage);

        return [
            'data' => $posts->getCollection()->map(function (Post $post) {
                return [
                    'id' => $post->id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'featured_image' => filled($post->featured_image)
                        ? uploaded_asset_details_from_ids($post->featured_image)
                        : null,
                    'summary' => $this->extractSummaryFromMeta($post),
                    'date' => $this->extractMetaValue($post, 'date'),
                    'time' => $this->extractMetaValue($post, 'time'),
                ];
            })->values()->all(),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ],
        ];
    }

    private function extractSummaryFromMeta(Post $post): ?string
    {
        if (!$post->relationLoaded('meta')) {
            $post->load('meta');
        }

        $summary = $post->meta->firstWhere('meta_key', 'short_summary')?->meta_value;
        if (filled($summary)) {
            return $summary;
        }

        $summary = $post->meta->firstWhere('meta_key', 'summary')?->meta_value;

        return filled($summary) ? $summary : null;
    }

    private function extractMetaValue(Post $post, string $key): ?string
    {
        if (!$post->relationLoaded('meta')) {
            $post->load('meta');
        }

        $value = $post->meta->firstWhere('meta_key', $key)?->meta_value;

        return filled($value) ? $value : null;
    }
}
