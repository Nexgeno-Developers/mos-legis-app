<?php

namespace App\Http\Controllers\Backend;

use App\Models\MenuGroup;
use App\Models\MenuItem;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;

class MenuController extends BaseController
{
    protected $moduleName;

    public function __construct()
    {
        $this->moduleName = 'Menus';
        view()->share('moduleName', $this->moduleName);

        $this->middleware('permission:menus view')->only(['index', 'getMenuItems']);
        $this->middleware('permission:menus create')->only(['saveGroup', 'saveItem']);
        $this->middleware('permission:menus edit')->only(['saveOrder']);
        $this->middleware('permission:menus delete')->only(['deleteGroup', 'deleteItem']);
    }

    /**
     * Show menu builder page
     */
    public function index(Request $request)
    {
        $menuGroups = MenuGroup::all();
        $selectedMenuGroup = $request->get('group', $menuGroups->first()->id ?? null);
        
        if ($selectedMenuGroup) {
            $menuItems = MenuItem::with('children')
                ->where('menu_group_id', $selectedMenuGroup)
                ->where('parent_id', null)
                ->orderBy('order')
                ->get();
        } else {
            $menuItems = collect();
        }

        return view('backend.menus.builder', compact('menuGroups', 'selectedMenuGroup', 'menuItems'));
    }

    /**
     * Create or update menu group or fetch existing group for editing
     */
    public function saveGroup(Request $request)
    {
        // If only id is provided, fetch the existing menu group
        if ($request->has('id') && !$request->has('name') && !$request->has('slug') && !$request->has('description') && !$request->has('status')) {
            $menuGroup = MenuGroup::find($request->id);
            
            if (!$menuGroup) {
                return response()->json(['success' => false, 'errors' => ['id' => 'Menu group not found']]);
            }
            
            return response()->json(['success' => true, 'menu_group' => $menuGroup]);
        }

        // Validation for creating/updating a menu group
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:menu_groups,slug,' . $request->get('id'),
            'description' => 'nullable|string',
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
        }

        $menuGroup = MenuGroup::updateOrCreate(
            ['id' => $request->get('id')],
            [
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'status' => $request->status ?? true,
            ]
        );

        ActivityLogService::store(
            'menus',
            $menuGroup->wasRecentlyCreated ? 'create' : 'update',
            (int) $menuGroup->id,
            $request->all(),
            $menuGroup->wasRecentlyCreated ? 'Menu group created' : 'Menu group updated'
        );

        return response()->json(['success' => true, 'menu_group' => $menuGroup]);
    }

    /**
     * Delete menu group
     */
    public function deleteGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:menu_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
        }

        $menuGroup = MenuGroup::findOrFail($request->id);

        ActivityLogService::store('menus', 'delete', (int) $menuGroup->id, [
            'name' => $menuGroup->name,
            'slug' => $menuGroup->slug,
        ], 'Menu group deleted');

        $menuGroup->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Create or update menu item or fetch existing item for editing
     */
    public function saveItem(Request $request)
    {
        // If only id is provided, fetch the existing menu item
        if ($request->has('id') && !$request->has('name') && !$request->has('url') && !$request->has('menu_group_id')) {
            $menuItem = MenuItem::find($request->id);
            
            if (!$menuItem) {
                return response()->json(['success' => false, 'errors' => ['id' => 'Menu item not found']]);
            }
            
            return response()->json(['success' => true, 'menu_item' => $menuItem]);
        }

        // Validation for creating/updating a menu item
        $validator = Validator::make($request->all(), [
            'menu_group_id' => 'required|exists:menu_groups,id',
            'name' => 'required|string|max:255',
            'url' => 'required|string',
            'icon' => 'nullable|string',
            'status' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
        }

        $menuItem = MenuItem::updateOrCreate(
            ['id' => $request->get('id')],
            [
                'menu_group_id' => $request->menu_group_id,
                //'parent_id' => $request->parent_id ?? null,
                'name' => $request->name,
                'url' => $request->url,
                'icon' => $request->icon,
                'status' => $request->status ?? true,
                //'order' => $request->order ?? 0,
            ]
        );

        ActivityLogService::store(
            'menus',
            $menuItem->wasRecentlyCreated ? 'create' : 'update',
            (int) $menuItem->id,
            $request->all(),
            $menuItem->wasRecentlyCreated ? 'Menu item created' : 'Menu item updated'
        );

        return response()->json(['success' => true, 'menu_item' => $menuItem]);
    }

    /**
     * Delete menu item
     */
    public function deleteItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:menu_items,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
        }

        $menuItem = MenuItem::findOrFail($request->id);

        ActivityLogService::store('menus', 'delete', (int) $menuItem->id, [
            'name' => $menuItem->name,
            'url' => $menuItem->url,
            'menu_group_id' => $menuItem->menu_group_id,
        ], 'Menu item deleted');

        $menuItem->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Save menu order and hierarchy
     */
    public function saveOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_group_id' => 'required|exists:menu_groups,id',
            'items' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
        }

        $this->saveMenuHierarchy($request->items, $request->menu_group_id);

        ActivityLogService::store(
            'menus',
            'update',
            (int) $request->menu_group_id,
            $request->all(),
            'Menu order updated'
        );

        return response()->json(['success' => true, 'message' => 'Menu order saved successfully']);
    }

    /**
     * Recursive function to save menu hierarchy
     */
    private function saveMenuHierarchy($items, $menuGroupId, $parentId = null, $order = 0)
    {
        foreach ($items as $index => $item) {
            $menuItem = MenuItem::find($item['id']);
            
            if ($menuItem) {
                $menuItem->update([
                    'menu_group_id' => $menuGroupId,
                    'parent_id' => $parentId,
                    'order' => $index,
                ]);

                if (isset($item['children']) && !empty($item['children'])) {
                    $this->saveMenuHierarchy($item['children'], $menuGroupId, $item['id'], 0);
                }
            }
        }
    }

    /**
     * Get menu items by group
     */
    public function getMenuItems(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'menu_group_id' => 'required|exists:menu_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()]);
        }

        $menuItems = MenuItem::with('children')
            ->where('menu_group_id', $request->menu_group_id)
            ->where('parent_id', null)
            ->orderBy('order')
            ->get();

        return response()->json(['success' => true, 'items' => $menuItems]);
    }
}
