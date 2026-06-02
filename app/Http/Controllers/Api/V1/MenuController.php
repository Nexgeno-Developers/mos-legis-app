<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Services\ApiPayloadCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class MenuController extends Controller
{
    /**
     * Get active menu group + active menu item tree by menu_group id.
     */
    public function showById(int $id): JsonResponse
    {
        $cached = ApiPayloadCache::getCachedMenuGroupPayload($id);
        if ($cached !== null) {
            return response()->json(['data' => $cached]);
        }

        $menuGroup = MenuGroup::query()
            ->where('id', $id)
            ->where('status', true)
            ->first();

        if (!$menuGroup) {
            return response()->json([
                'error' => [
                    'message' => 'Menu group not found',
                    'code' => 'MENU_GROUP_NOT_FOUND',
                ],
            ], 404);
        }

        $data = [
            'menu_group' => $this->menuGroupPayload($menuGroup),
            'items' => $this->menuItemsTreePayload($menuGroup->id),
        ];
        ApiPayloadCache::storeMenuGroupPayload((int) $menuGroup->id, $data);

        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * Get active menu group + active menu item tree by menu_group name.
     * If multiple groups match the name (non-unique in DB), returns an array.
     */
    public function showByName(string $name): JsonResponse
    {
        $menuGroups = MenuGroup::query()
            ->where('name', $name)
            ->where('status', true)
            ->orderBy('id')
            ->get();

        if ($menuGroups->isEmpty()) {
            return response()->json([
                'error' => [
                    'message' => 'Menu group not found',
                    'code' => 'MENU_GROUP_NOT_FOUND',
                ],
            ], 404);
        }

        if ($menuGroups->count() === 1) {
            $menuGroup = $menuGroups->first();
            $groupId = (int) $menuGroup->id;

            $cached = ApiPayloadCache::getCachedMenuGroupPayload($groupId);
            if ($cached !== null) {
                return response()->json(['data' => $cached]);
            }

            $data = [
                'menu_group' => $this->menuGroupPayload($menuGroup),
                'items' => $this->menuItemsTreePayload($menuGroup->id),
            ];
            ApiPayloadCache::storeMenuGroupPayload($groupId, $data);

            return response()->json([
                'data' => $data,
            ]);
        }

        $data = $menuGroups->map(function (MenuGroup $menuGroup) {
            $groupId = (int) $menuGroup->id;

            $cached = ApiPayloadCache::getCachedMenuGroupPayload($groupId);
            if ($cached !== null) {
                return $cached;
            }

            $built = [
                'menu_group' => $this->menuGroupPayload($menuGroup),
                'items' => $this->menuItemsTreePayload($menuGroup->id),
            ];
            ApiPayloadCache::storeMenuGroupPayload($groupId, $built);

            return $built;
        })->values();

        return response()->json([
            'data' => $data,
        ]);
    }

    private function menuGroupPayload(MenuGroup $menuGroup): array
    {
        return [
            'id' => $menuGroup->id,
            'name' => $menuGroup->name,
            'slug' => $menuGroup->slug,
            'description' => $menuGroup->description,
            'status' => (bool) $menuGroup->status,
        ];
    }

    /**
     * Build a nested tree (roots -> children -> grandchildren ...) for active MenuItems.
     *
     * Returns an array suitable for JSON serialization.
     */
    private function menuItemsTreePayload(int $menuGroupId): array
    {
        $items = MenuItem::query()
            ->where('menu_group_id', $menuGroupId)
            ->where('status', true)
            ->orderBy('order')
            ->get();

        $childrenByParent = $items->groupBy('parent_id');

        $build = function (?int $parentId) use (&$build, $childrenByParent): array {
            /** @var Collection<int, MenuItem> $children */
            $children = $childrenByParent->get($parentId, collect());

            return $children
                ->values()
                ->map(function (MenuItem $item) use (&$build) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'url' => $item->url,
                        'icon' => $item->icon,
                        'order' => $item->order,
                        'status' => (bool) $item->status,
                        'parent_id' => $item->parent_id,
                        'children' => $build($item->id),
                    ];
                })
                ->all();
        };

        return $build(null);
    }
}

