<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Services\ApiPayloadCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SitemapController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $types = $this->normalizeTypes($request->get('types'));
        $includePostAliases = $request->boolean('include_post_aliases', false);
        $frontendBaseUrl = $this->frontendBaseUrl();

        $companyId = config('custom.company_id');
        if (!empty($companyId)) {
            $companyId = (int) $companyId;
        } else {
            $companyId = null;
        }

        $cached = ApiPayloadCache::getCachedSitemapPayload($types, $companyId, $includePostAliases);
        if ($cached !== null) {
            return response()->json(['data' => $cached]);
        }

        $payload = [
            'generated_at' => now()->toISOString(),
            'company_id' => $companyId,
            'types' => $types,
            'pages' => [],
            'posts' => [],
            'categories' => [],
        ];

        if (in_array('pages', $types, true)) {
            $pagesQuery = Page::query()
                ->select(['slug', 'updated_at'])
                ->where('is_active', true)
                ->orderByRaw('CASE WHEN slug = ? OR slug = ? THEN 0 ELSE 1 END', ['home', ''])
                ->orderBy('slug');

            if (!empty($companyId)) {
                $pagesQuery->where('company_id', $companyId);
            }

            $payload['pages'] = $pagesQuery->get()->map(function (Page $page) use ($frontendBaseUrl) {
                $normalizedSlug = trim((string) $page->slug, '/');
                $isHome = ($normalizedSlug === '' || $normalizedSlug === 'home');

                return [
                    'slug' => $isHome
                        ? '/'
                        : $this->withFrontendDomain($normalizedSlug, $frontendBaseUrl),
                    'lastmod' => $page->updated_at?->toDateString(),
                ];
            })->values()->all();
        }

        if (in_array('posts', $types, true)) {
            $postsQuery = Post::query()
                ->select(['id', 'slug', 'auto_slug', 'published_at', 'updated_at'])
                ->with([
                    'categories' => function ($q) {
                        $q->select(['categories.id', 'categories.slug', 'categories.parent_id', 'categories.is_active'])
                            ->where('categories.is_active', true);
                    },
                    'categories.parent' => function ($q) {
                        $q->select(['categories.id', 'categories.slug']);
                    },
                ])
                ->where('is_active', true)
                ->orderByDesc('published_at');

            if (!empty($companyId)) {
                $postsQuery->where('company_id', $companyId);
            }

            $payload['posts'] = $postsQuery->get()->flatMap(function (Post $post) use ($includePostAliases, $frontendBaseUrl) {
                $postSlug = trim((string) $post->slug, '/');

                $categoryPrefixes = $post->categories
                    ->map(function (Category $category) {
                        $parentSlug = $category->parent?->slug;

                        return trim((string) ($parentSlug ?: $category->slug), '/');
                    })
                    ->filter(static fn ($slug) => $slug !== '')
                    ->unique()
                    ->values()
                    ->all();

                $slugCandidates = [$postSlug];

                if ($includePostAliases && is_array($post->auto_slug)) {
                    foreach ($post->auto_slug as $alias) {
                        if (!filled($alias)) {
                            continue;
                        }

                        $normalized = trim((string) $alias, '/');
                        if ($normalized === '' || in_array($normalized, $slugCandidates, true)) {
                            continue;
                        }

                        $slugCandidates[] = $normalized;
                    }
                }

                $rows = [];
                $seenUrls = [];
                $lastmod = $post->updated_at?->toDateString();
                $publishedAt = $post->published_at?->toDateString();

                foreach ($slugCandidates as $candidate) {
                    if ($candidate === '') {
                        continue;
                    }

                    if (empty($categoryPrefixes)) {
                        $url = $this->withFrontendDomain($candidate, $frontendBaseUrl);
                        if (!isset($seenUrls[$url])) {
                            $seenUrls[$url] = true;
                            $rows[] = [
                                'slug' => $url,
                                'lastmod' => $lastmod,
                                'published_at' => $publishedAt,
                            ];
                        }

                        continue;
                    }

                    foreach ($categoryPrefixes as $prefix) {
                        $path = $candidate;
                        $prefixWithSlash = $prefix . '/';
                        if ($prefix !== '' && ! str_starts_with($candidate, $prefixWithSlash)) {
                            $path = $prefixWithSlash . $candidate;
                        }

                        $url = $this->withFrontendDomain($path, $frontendBaseUrl);

                        if (isset($seenUrls[$url])) {
                            continue;
                        }

                        $seenUrls[$url] = true;
                        $rows[] = [
                            'slug' => $url,
                            'lastmod' => $lastmod,
                            'published_at' => $publishedAt,
                        ];
                    }
                }

                return $rows;
            })->values()->all();
        }

        if (in_array('categories', $types, true)) {
            $categoriesQuery = Category::query()
                ->select(['slug', 'updated_at'])
                ->where('is_active', true)
                ->orderBy('slug');

            if (!empty($companyId)) {
                $categoriesQuery->where('company_id', $companyId);
            }

            $payload['categories'] = $categoriesQuery->get()->map(function (Category $category) use ($frontendBaseUrl) {
                $normalizedSlug = trim((string) $category->slug, '/');

                return [
                    'slug' => $this->withFrontendDomain($normalizedSlug, $frontendBaseUrl),
                    'lastmod' => $category->updated_at?->toDateString(),
                ];
            })->values()->all();
        }

        ApiPayloadCache::storeSitemapPayload($types, $companyId, $includePostAliases, $payload);

        return response()->json(['data' => $payload]);
    }

    /**
     * @return array<int, string>
     */
    private function normalizeTypes($raw): array
    {
        if ($raw === null || $raw === '') {
            return ['pages', 'posts', 'categories'];
        }

        $parts = array_map('trim', explode(',', (string) $raw));
        $parts = array_values(array_filter($parts, static fn ($p) => $p !== ''));
        $parts = array_map(static fn ($p) => strtolower($p), $parts);
        $parts = array_values(array_unique($parts));

        $allowed = ['pages', 'posts', 'categories'];
        $parts = array_values(array_filter($parts, static fn ($p) => in_array($p, $allowed, true)));

        sort($parts, SORT_STRING);

        return empty($parts) ? ['pages', 'posts', 'categories'] : $parts;
    }

    private function frontendBaseUrl(): string
    {
        $fromConfig = (string) config('custom.frontend_url', '');
        $fromEnv = (string) env('FRONTEND_URL', '');

        $base = $fromConfig !== '' ? $fromConfig : $fromEnv;
        $base = trim($base);

        return rtrim($base, '/');
    }

    private function withFrontendDomain(string $slug, string $base): string
    {
        $normalizedSlug = trim($slug, '/');
        if ($base === '') {
            return $normalizedSlug;
        }

        if ($normalizedSlug === '') {
            return $base . '/';
        }

        return $base . '/' . $normalizedSlug;
    }
}
