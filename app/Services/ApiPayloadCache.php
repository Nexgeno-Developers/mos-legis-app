<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Centralized caching for public API payloads (Page, Menu group, Company).
 *
 * Uses revision counters so all cache drivers (database, file, redis, …) can
 * invalidate without relying on tags. Payload keys include:
 * - per-page revision (content, meta, slug, active flag, … + SEO via slug hook)
 * - global "autofetch graph" revision when ?autofetch= is used, so list-style
 *   sections stay correct when any page changes.
 */
class ApiPayloadCache
{
    public static function ttlSeconds(): int
    {
        return max(60, (int) config('api_payload_cache.ttl_seconds', 604800));
    }

    private static function revisionTtlSeconds(): int
    {
        return max(3600, (int) config('api_payload_cache.revision_ttl_seconds', 31536000));
    }

    private static function keyVersion(): string
    {
        return (string) config('api_payload_cache.key_version', 'v1');
    }

    private static function basePrefix(): string
    {
        return 'api_payload:' . self::keyVersion();
    }

    public static function normalizeAutofetch(?string $raw): string
    {
        if ($raw === null || $raw === '') {
            return '';
        }

        $parts = array_map('trim', explode(',', $raw));
        $parts = array_values(array_filter($parts, static fn ($p) => $p !== ''));
        sort($parts, SORT_STRING);

        return implode(',', $parts);
    }

    private static function pageLocalRevisionKey(int $pageId): string
    {
        return self::basePrefix() . ':rev:page:' . $pageId;
    }

    private static function postLocalRevisionKey(int $postId): string
    {
        return self::basePrefix() . ':rev:post:' . $postId;
    }

    private static function globalAutofetchRevisionKey(): string
    {
        return self::basePrefix() . ':rev:pages:autofetch';
    }

    private static function globalPostsRevisionKey(): string
    {
        return self::basePrefix() . ':rev:posts:graph';
    }

    private static function globalCategoriesRevisionKey(): string
    {
        return self::basePrefix() . ':rev:categories:graph';
    }

    private static function menuGroupRevisionKey(int $menuGroupId): string
    {
        return self::basePrefix() . ':rev:menu_group:' . $menuGroupId;
    }

    private static function companyRevisionKey(int $companyId): string
    {
        return self::basePrefix() . ':rev:company:' . $companyId;
    }

    private static function robotsTxtRevisionKey(int $companyId): string
    {
        return self::basePrefix() . ':rev:robots_txt:' . $companyId;
    }

    private static function getIntRevision(string $key): int
    {
        try {
            $v = Cache::get($key, 0);

            return is_numeric($v) ? (int) $v : 0;
        } catch (Throwable $e) {
            Log::warning('ApiPayloadCache: failed to read revision', [
                'key' => $key,
                'message' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Increment a revision counter stored in cache (driver-agnostic).
     */
    private static function incrementRevision(string $key): void
    {
        $ttl = now()->addSeconds(self::revisionTtlSeconds());

        try {
            if (! Cache::has($key)) {
                Cache::put($key, 0, $ttl);
            }

            $inc = Cache::increment($key);
            if ($inc === false) {
                $current = (int) Cache::get($key, 0);
                Cache::put($key, $current + 1, $ttl);
            }
        } catch (Throwable $e) {
            try {
                $current = (int) Cache::get($key, 0);
                Cache::put($key, $current + 1, $ttl);
            } catch (Throwable $inner) {
                Log::error('ApiPayloadCache: failed to bump revision', [
                    'key' => $key,
                    'message' => $inner->getMessage(),
                ]);
            }
        }
    }

    private static function pagePayloadKey(int $pageId, string $autofetchNormalized): string
    {
        $local = self::getIntRevision(self::pageLocalRevisionKey($pageId));

        if ($autofetchNormalized === '') {
            return self::basePrefix() . ':page:' . $pageId . ':r:' . $local;
        }

        $afTag = hash('sha256', $autofetchNormalized);
        $global = self::getIntRevision(self::globalAutofetchRevisionKey());

        return self::basePrefix()
            . ':page:' . $pageId
            . ':af:' . $afTag
            . ':g:' . $global
            . ':r:' . $local;
    }

    private static function postPayloadKey(int $postId): string
    {
        $local = self::getIntRevision(self::postLocalRevisionKey($postId));
        $global = self::getIntRevision(self::globalPostsRevisionKey());

        return self::basePrefix() . ':post:' . $postId . ':g:' . $global . ':r:' . $local;
    }

    private static function menuPayloadKey(int $menuGroupId): string
    {
        $rev = self::getIntRevision(self::menuGroupRevisionKey($menuGroupId));

        return self::basePrefix() . ':menu_group:' . $menuGroupId . ':r:' . $rev;
    }

    private static function companyPayloadKey(int $companyId): string
    {
        $rev = self::getIntRevision(self::companyRevisionKey($companyId));

        return self::basePrefix() . ':company:' . $companyId . ':r:' . $rev;
    }

    private static function sitemapPayloadKey(?int $companyId, string $typesNormalized, bool $includePostAliases): string
    {
        $schemaVersion = 'sv4';
        $pagesGraph = self::getIntRevision(self::globalAutofetchRevisionKey());
        $postsGraph = self::getIntRevision(self::globalPostsRevisionKey());
        $categoriesGraph = self::getIntRevision(self::globalCategoriesRevisionKey());

        $typesTag = $typesNormalized === '' ? 'all' : hash('sha256', $typesNormalized);
        $aliasTag = $includePostAliases ? 'a1' : 'a0';
        $companyTag = empty($companyId) ? 'c0' : ('c' . $companyId);

        return self::basePrefix()
            . ':sitemap:' . $companyTag
            . ':' . $schemaVersion
            . ':t:' . $typesTag
            . ':' . $aliasTag
            . ':p:' . $pagesGraph
            . ':b:' . $postsGraph
            . ':k:' . $categoriesGraph;
    }

    private static function robotsTxtPayloadKey(int $companyId): string
    {
        $schemaVersion = 'sv1';
        $localRev = self::getIntRevision(self::robotsTxtRevisionKey($companyId));
        $globalRev = $companyId === 0
            ? $localRev
            : self::getIntRevision(self::robotsTxtRevisionKey(0));
        $companyTag = $companyId <= 0 ? 'c0' : ('c' . $companyId);

        return self::basePrefix()
            . ':robots_txt:' . $companyTag
            . ':' . $schemaVersion
            . ':g:' . $globalRev
            . ':r:' . $localRev;
    }

    /**
     * @return array<string, mixed>|null  Cached payload, or null if missing / invalid / read error.
     */
    public static function getCachedPagePayload(int $pageId, ?string $autofetchRaw): ?array
    {
        $key = self::pagePayloadKey($pageId, self::normalizeAutofetch($autofetchRaw));

        return self::getPayloadArray($key);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function storePagePayload(int $pageId, ?string $autofetchRaw, array $payload): void
    {
        $key = self::pagePayloadKey($pageId, self::normalizeAutofetch($autofetchRaw));
        self::storePayloadArray($key, $payload);
    }

    /**
     * @return array<string, mixed>|null  Cached payload, or null if missing / invalid / read error.
     */
    public static function getCachedRobotsTxtPayload(int $companyId): ?array
    {
        return self::getPayloadArray(self::robotsTxtPayloadKey($companyId));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function storeRobotsTxtPayload(int $companyId, array $payload): void
    {
        self::storePayloadArray(self::robotsTxtPayloadKey($companyId), $payload);
    }

    /**
     * @return array<string, mixed>|null  Cached payload, or null if missing / invalid / read error.
     */
    public static function getCachedPostPayload(int $postId): ?array
    {
        return self::getPayloadArray(self::postPayloadKey($postId));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function storePostPayload(int $postId, array $payload): void
    {
        self::storePayloadArray(self::postPayloadKey($postId), $payload);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getCachedMenuGroupPayload(int $menuGroupId): ?array
    {
        return self::getPayloadArray(self::menuPayloadKey($menuGroupId));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function storeMenuGroupPayload(int $menuGroupId, array $payload): void
    {
        self::storePayloadArray(self::menuPayloadKey($menuGroupId), $payload);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function getCachedCompanyPayload(int $companyId): ?array
    {
        return self::getPayloadArray(self::companyPayloadKey($companyId));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function storeCompanyPayload(int $companyId, array $payload): void
    {
        self::storePayloadArray(self::companyPayloadKey($companyId), $payload);
    }

    /**
     * @param  array<int, string>  $types
     * @return array<string, mixed>|null
     */
    public static function getCachedSitemapPayload(array $types, ?int $companyId, bool $includePostAliases): ?array
    {
        $typesNormalized = implode(',', $types);

        return self::getPayloadArray(self::sitemapPayloadKey($companyId, $typesNormalized, $includePostAliases));
    }

    /**
     * @param  array<int, string>  $types
     * @param  array<string, mixed>  $payload
     */
    public static function storeSitemapPayload(array $types, ?int $companyId, bool $includePostAliases, array $payload): void
    {
        $typesNormalized = implode(',', $types);

        self::storePayloadArray(self::sitemapPayloadKey($companyId, $typesNormalized, $includePostAliases), $payload);
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function getPayloadArray(string $key): ?array
    {
        try {
            $value = Cache::get($key);

            return is_array($value) ? $value : null;
        } catch (Throwable $e) {
            Log::warning('ApiPayloadCache: cache get failed', [
                'key' => $key,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function storePayloadArray(string $key, array $payload): void
    {
        try {
            Cache::put($key, $payload, now()->addSeconds(self::ttlSeconds()));
        } catch (Throwable $e) {
            Log::warning('ApiPayloadCache: cache put failed', [
                'key' => $key,
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Call after page create/update/delete/clone or anything that changes which
     * pages appear in autofetch list sections.
     */
    public static function invalidatePage(int $pageId, bool $bumpAutofetchGraph = true): void
    {
        self::incrementRevision(self::pageLocalRevisionKey($pageId));
        if ($bumpAutofetchGraph) {
            self::incrementRevision(self::globalAutofetchRevisionKey());
        }
    }

    /**
     * Call after post create/update/delete or anything that changes public post payload.
     * Also bumps the page autofetch graph so page sections relying on posts stay fresh.
     */
    public static function invalidatePost(
        int $postId,
        bool $bumpPageAutofetchGraph = true,
        bool $bumpPostsGraph = true
    ): void
    {
        self::incrementRevision(self::postLocalRevisionKey($postId));

        if ($bumpPostsGraph) {
            self::incrementRevision(self::globalPostsRevisionKey());
        }

        if ($bumpPageAutofetchGraph) {
            self::incrementRevision(self::globalAutofetchRevisionKey());
        }
    }

    public static function invalidatePostBySlug(?string $slug): void
    {
        if ($slug === null || $slug === '') {
            return;
        }

        $postId = Post::query()
            ->where('slug', $slug)
            ->orWhereJsonContains('auto_slug', $slug)
            ->value('id');

        if ($postId !== null) {
            self::invalidatePost((int) $postId, true);
        }
    }

    /**
     * SEO rows are merged into the page API by matching `pages.slug`. This only
     * needs a local bump for the affected page(s) — not the global autofetch graph.
     */
    public static function invalidatePageBySlug(?string $slug): void
    {
        if ($slug === null || $slug === '') {
            return;
        }

        $pageId = Page::query()->where('slug', $slug)->value('id');
        if ($pageId !== null) {
            self::invalidatePage((int) $pageId, false);
        }
    }

    public static function invalidateMenuGroup(int $menuGroupId): void
    {
        self::incrementRevision(self::menuGroupRevisionKey($menuGroupId));
    }

    /**
     * Call after category create/update/delete or anything that changes which
     * categories appear publicly (slug, active flag, parent, …).
     *
     * Also bumps the posts graph because post payloads include category slugs.
     */
    public static function invalidateCategory(
        int $categoryId,
        bool $bumpPageAutofetchGraph = true,
        bool $bumpPostsGraph = true,
        bool $bumpCategoriesGraph = true
    ): void
    {
        // categoryId is currently unused but kept for future per-category revisions.
        unset($categoryId);

        if ($bumpCategoriesGraph) {
            self::incrementRevision(self::globalCategoriesRevisionKey());
        }

        if ($bumpPostsGraph) {
            self::incrementRevision(self::globalPostsRevisionKey());
        }

        if ($bumpPageAutofetchGraph) {
            self::incrementRevision(self::globalAutofetchRevisionKey());
        }
    }

    public static function invalidateCompany(int $companyId): void
    {
        self::incrementRevision(self::companyRevisionKey($companyId));
    }

    public static function invalidateRobotsTxt(int $companyId): void
    {
        self::incrementRevision(self::robotsTxtRevisionKey($companyId));
    }
}
