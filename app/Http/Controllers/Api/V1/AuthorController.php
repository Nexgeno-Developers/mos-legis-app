<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Author::query()->where('is_active', true);

        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        $authors = $query->orderBy('name')->get();

        return response()->json([
            'data' => $authors->map(fn ($author) => [
                'id' => $author->id,
                'name' => $author->name,
                'email' => $author->email,
                'bio' => $author->bio,
                'profile_image' => filled($author->profile_image)
                    ? uploaded_asset_details_from_ids($author->profile_image)
                    : null,
                'company_id' => $author->company_id,
            ])->values()->all(),
        ]);
    }

    public function showById(int $id, Request $request): JsonResponse
    {
        $query = Author::query()->where('id', $id)->where('is_active', true);

        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $query->where('company_id', $companyId);
        }

        $author = $query->first();

        if (!$author) {
            return response()->json([
                'error' => [
                    'message' => 'Author not found',
                    'code' => 'AUTHOR_NOT_FOUND',
                ],
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $author->id,
                'name' => $author->name,
                'email' => $author->email,
                'bio' => $author->bio,
                'profile_image' => filled($author->profile_image)
                    ? uploaded_asset_details_from_ids($author->profile_image)
                    : null,
                'company_id' => $author->company_id,
            ],
        ]);
    }

    public function postsById(int $id, Request $request): JsonResponse
    {
        $authorQuery = Author::where('id', $id)->where('is_active', true);
        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $authorQuery->where('company_id', $companyId);
        }

        $author = $authorQuery->first();

        if (!$author) {
            return response()->json([
                'error' => [
                    'message' => 'Author not found',
                    'code' => 'AUTHOR_NOT_FOUND',
                ],
            ], 404);
        }

        $postsQuery = $author->posts()
            ->where('is_active', true)
            ->with(['categories', 'tags'])
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
        ]);
    }
}
