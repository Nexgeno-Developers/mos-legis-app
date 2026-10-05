<?php

namespace App\Support;

use App\Enums\MenuLinkType;
use App\Enums\MenuLocation;
use App\Enums\PublishStatus;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/**
 * Builds a website menu for rendering. The item tree is cached and cleared whenever a
 * menu, menu item or page changes; URLs and the active state are resolved per request.
 * Inactive items, unpublished pages and groups without visible links are left out.
 */
final class SiteMenu
{
    private const CACHE_KEY = 'site-menu.';

    /**
     * @return list<array{label: string, url: ?string, new_tab: bool, active: bool, children: list<array{label: string, url: string, new_tab: bool, active: bool}>}>
     */
    public static function for(MenuLocation $location): array
    {
        $items = [];

        foreach (self::cached($location) as $item) {
            if ($item['link_type'] === MenuLinkType::None->value) {
                $children = array_values(array_filter(array_map(self::resolve(...), $item['children'])));

                if ($children) {
                    $items[] = [
                        'label' => $item['label'],
                        'url' => null,
                        'new_tab' => false,
                        'active' => in_array(true, array_column($children, 'active'), true),
                        'children' => $children,
                    ];
                }

                continue;
            }

            if ($link = self::resolve($item)) {
                $items[] = $link + ['children' => []];
            }
        }

        return $items;
    }

    public static function flush(): void
    {
        foreach (MenuLocation::cases() as $location) {
            Cache::forget(self::CACHE_KEY.$location->value);
        }
    }

    /** @return list<array<string, mixed>> */
    private static function cached(MenuLocation $location): array
    {
        return Cache::rememberForever(self::CACHE_KEY.$location->value, function () use ($location) {
            $menu = Menu::where('location', $location)->first();

            if (! $menu) {
                return [];
            }

            return $menu->tree()->active()->get()->map(fn (MenuItem $item) => self::toArray($item) + [
                'children' => $item->children->filter->isActive()->map(self::toArray(...))->values()->all(),
            ])->all();
        });
    }

    /** @return array<string, mixed> */
    private static function toArray(MenuItem $item): array
    {
        return [
            'label' => $item->label,
            'link_type' => $item->link_type->value,
            'route_name' => $item->route_name,
            'page_slug' => $item->page?->status === PublishStatus::Published ? $item->page->slug : null,
            'url' => $item->url,
            'new_tab' => $item->open_in_new_tab,
        ];
    }

    /** @return array{label: string, url: string, new_tab: bool, active: bool}|null */
    private static function resolve(array $item): ?array
    {
        [$url, $active] = match ($item['link_type']) {
            MenuLinkType::Route->value => Route::has((string) $item['route_name'])
                ? [route($item['route_name']), request()->routeIs($item['route_name'], str_replace('.index', '', $item['route_name']).'.*')]
                : [null, false],
            MenuLinkType::Page->value => $item['page_slug']
                ? [$item['page_slug'] === 'home' ? route('home') : url($item['page_slug']), request()->path() === ($item['page_slug'] === 'home' ? '/' : $item['page_slug'])]
                : [null, false],
            MenuLinkType::Url->value => [$item['url'], rtrim((string) $item['url'], '/') === rtrim(url()->current(), '/')],
            default => [null, false],
        };

        return $url ? ['label' => $item['label'], 'url' => $url, 'new_tab' => (bool) $item['new_tab'], 'active' => $active] : null;
    }
}
