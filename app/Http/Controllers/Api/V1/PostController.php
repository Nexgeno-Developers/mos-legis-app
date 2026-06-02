<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\SeoMeta;
use App\Services\ApiPayloadCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    private array $uploadMetaKeys;
    private array $jsonMetaKeys;
    private array $repeaterFields;

    public function __construct()
    {
        $this->uploadMetaKeys = getPostMetaUploadKeys();
        $this->jsonMetaKeys = getPostMetaJsonKeys();
        $this->repeaterFields = getPostRepeaterFieldsMap();
    }

    public function index(Request $request): JsonResponse
    {
        $query = Post::query()->with(['meta', 'author', 'categories', 'tags']);

        $query->where('is_active', true);

        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        if ($request->filled('category')) {
            $categorySlug = normalize_post_slug($request->get('category'));
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if ($request->filled('tag')) {
            $tagSlug = normalize_post_slug($request->get('tag'));
            $query->whereHas('tags', function ($q) use ($tagSlug) {
                $q->where('slug', $tagSlug);
            });
        }

        $authorId = $request->get('author_id', $request->get('author'));
        if (filled($authorId) && ctype_digit((string) $authorId)) {
            $query->where('author_id', (int) $authorId);
        }

        $perPage = max(1, min((int) $request->get('per_page', 10), 50));
        $posts = $query->orderByDesc('published_at')->paginate($perPage);

        $includeContent = $request->boolean('with_content', true);
        $data = $posts->getCollection()->map(function (Post $post) use ($includeContent) {
            return $this->postPayload($post, $includeContent, false);
        });

        return response()->json([
            'data' => $data,
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
                'last_page' => $posts->lastPage(),
            ],
        ]);
    }

    public function showBySlug(string $slug, Request $request): JsonResponse
    {
        $normalizedSlug = normalize_post_slug(rawurldecode($slug));
        $companyId = config('custom.company_id');

        $postIdQuery = Post::query()
            ->where('is_active', true)
            ->where(function ($q) use ($normalizedSlug) {
                $q->where('slug', $normalizedSlug)
                    ->orWhereJsonContains('auto_slug', $normalizedSlug);
            });

        if (!empty($companyId)) {
            $postIdQuery->where('company_id', $companyId);
        }

        $postId = $postIdQuery->value('id');

        if ($postId === null) {
            return response()->json([
                'error' => [
                    'message' => 'Post not found',
                    'code' => 'POST_NOT_FOUND',
                ],
            ], 404);
        }

        $postId = (int) $postId;

        $cached = ApiPayloadCache::getCachedPostPayload($postId);
        if ($cached !== null) {
            // Payload schema guard: if we add new top-level keys, old cached payloads
            // may not include them until TTL expires. Rebuild once and store fresh.
            if (array_key_exists('date', $cached) && array_key_exists('time', $cached)) {
                return response()->json(['data' => $cached]);
            }
        }

        $post = Post::query()
            ->with(['meta', 'author', 'categories', 'tags'])
            ->where('id', $postId)
            ->where('is_active', true)
            ->when(!empty($companyId), function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->first();

        if (!$post) {
            return response()->json([
                'error' => [
                    'message' => 'Post not found',
                    'code' => 'POST_NOT_FOUND',
                ],
            ], 404);
        }

        $payload = $this->postPayload($post, true, true);
        ApiPayloadCache::storePostPayload($postId, $payload);

        return response()->json(['data' => $payload]);
    }

    private function postPayload(Post $post, bool $includeContent, bool $includeRelated): array
    {
        $relatedPosts = $includeRelated ? $this->relatedPostsFor($post) : [];
        $seoMeta = $includeRelated
            ? SeoMeta::query()->where('slug', $post->slug)->first()
            : null;

        $meta = $post->meta
            ->mapWithKeys(function ($m) {
                $value = $m->meta_value;

                if (array_key_exists($m->meta_key, $this->repeaterFields)) {
                    $decoded = json_decode($value, true);
                    $decoded = is_array($decoded) ? $decoded : [];
                    $value = post_build_repeater_blocks($decoded, $this->repeaterFields[$m->meta_key]);
                } elseif (in_array($m->meta_key, $this->jsonMetaKeys, true)) {
                    $decoded = json_decode($value, true);
                    $value = is_array($decoded) ? $decoded : $value;
                }
                if (in_array($m->meta_key, $this->uploadMetaKeys, true)) {
                    $value = filled($value) ? uploaded_asset_details_from_ids($value) : null;
                }

                return [$m->meta_key => $value];
            })
            ->all();

        return [
            'id' => $post->id,
            'slug' => $post->slug,
            'auto_slug' => $post->auto_slug ?? [],
            'language' => $post->language,
            'title' => $post->title,
            'content' => $includeContent ? $post->content : null,
            'featured_image' => filled($post->featured_image)
                ? uploaded_asset_details_from_ids($post->featured_image)
                : null,
            'layout' => array_key_exists($post->layout, getPostLayouts()) ? $post->layout : 'default_post_detail',
            'is_active' => (bool) $post->is_active,
            'company_id' => $post->company_id,
            'summary' => $meta['short_summary'] ?? $meta['summary'] ?? null,
            'date' => $meta['date'] ?? null,
            'time' => $meta['time'] ?? null,
            'author' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
                'email' => $post->author->email,
                'profile_image' => filled($post->author->profile_image)
                    ? uploaded_asset_details_from_ids($post->author->profile_image)
                    : null,
            ] : null,
            'categories' => $post->categories
                ->map(fn ($category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent_id' => $category->parent_id,
                ])
                ->values()
                ->all(),
            'tags' => $post->tags
                ->map(fn ($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])
                ->values()
                ->all(),
            'meta' => $meta,
            'seo' => $this->buildSeoPayload($post, $seoMeta),
            'published_at' => $post->published_at?->toISOString(),
            'created_at' => $post->created_at?->toISOString(),
            'updated_at' => $post->updated_at?->toISOString(),
            'related_posts' => $relatedPosts,
        ];
    }

    private function buildSeoPayload(Post $post, ?SeoMeta $seoMeta = null): array
    {
        return [
            'title' => filled($seoMeta?->meta_title) ? $seoMeta->meta_title : $post->seo_title,
            'description' => filled($seoMeta?->meta_description) ? $seoMeta->meta_description : $post->seo_description,
            'keywords' => filled($seoMeta?->meta_keywords) ? $seoMeta->meta_keywords : $post->seo_keywords,
            'schema' => filled($seoMeta?->schema_json) ? $seoMeta->schema_json : $post->seo_schema,
            'canonical_url' => $seoMeta?->canonical_url,
            'robots_index' => $seoMeta?->robots_index,
            'robots_follow' => $seoMeta?->robots_follow,
            'og_title' => $seoMeta?->og_title,
            'og_description' => $seoMeta?->og_description,
            'og_image' => filled($seoMeta?->og_image)
                ? uploaded_asset_details_from_ids($seoMeta->og_image)
                : null,
            'twitter_title' => $seoMeta?->twitter_title,
            'twitter_description' => $seoMeta?->twitter_description,
            'twitter_image' => filled($seoMeta?->twitter_image)
                ? uploaded_asset_details_from_ids($seoMeta->twitter_image)
                : null,
            'sitemap_priority' => $seoMeta?->sitemap_priority,
        ];
    }

    private function relatedPostsFor(Post $post): array
    {
        $categoryIds = $post->categories->pluck('id')->toArray();
        if (empty($categoryIds)) {
            return [];
        }

        $query = Post::query()
            ->with('meta')
            ->where('is_active', true)
            ->where('id', '!=', $post->id)
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->orderByDesc('published_at')
            ->limit(3);

        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        return $query->get()->map(function (Post $related) {
            $summary = $this->extractSummaryFromMeta($related);
            return [
                'title' => $related->title,
                'slug' => $related->slug,
                'featured_image' => filled($related->featured_image)
                    ? uploaded_asset_details_from_ids($related->featured_image)
                    : null,
                'summary' => $summary,
                'published_at' => $related->published_at?->toISOString(),
            ];
        })->values()->all();
    }

    private function extractSummaryFromMeta(Post $post): ?string
    {
        $summary = $post->meta->firstWhere('meta_key', 'short_summary')?->meta_value;
        if (filled($summary)) {
            return $summary;
        }

        $summary = $post->meta->firstWhere('meta_key', 'summary')?->meta_value;

        return filled($summary) ? $summary : null;
    }
}
