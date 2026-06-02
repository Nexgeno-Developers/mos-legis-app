<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Tag::query();

        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        $tags = $query->orderBy('name')->get();

        return response()->json([
            'data' => $tags->map(fn ($tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'slug' => $tag->slug,
                'company_id' => $tag->company_id,
            ])->values()->all(),
        ]);
    }

    public function postsBySlug(string $slug, Request $request): JsonResponse
    {
        $normalizedSlug = normalize_post_slug(rawurldecode($slug));
        $tagQuery = Tag::where('slug', $normalizedSlug);
        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $tagQuery->where('company_id', $companyId);
        }

        $tag = $tagQuery->first();

        if (!$tag) {
            return response()->json([
                'error' => [
                    'message' => 'Tag not found',
                    'code' => 'TAG_NOT_FOUND',
                ],
            ], 404);
        }

        $postsQuery = $tag->posts()
            ->where('is_active', true)
            ->with(['author', 'categories', 'tags'])
            ->orderByDesc('published_at');

        if (!empty($companyId)) {
            $postsQuery->where('company_id', $companyId);
        }

        $posts = $postsQuery->get();

        return response()->json([
            'data' => $posts->map(fn ($post) => [
                'id' => $post->id,
                'slug' => $post->slug,
                'title' => $post->title,
                'layout' => $post->layout,
                'featured_image' => filled($post->featured_image)
                    ? uploaded_asset_details_from_ids($post->featured_image)
                    : null,
                'author' => $post->author ? [
                    'id' => $post->author->id,
                    'name' => $post->author->name,
                ] : null,
                'categories' => $post->categories->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                ])->values()->all(),
                'tags' => $post->tags->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'slug' => $t->slug,
                ])->values()->all(),
                'published_at' => $post->published_at?->toISOString(),
            ])->values()->all(),
        ]);
    }
}
