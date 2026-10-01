<?php

namespace App\Http\Controllers\Admin;

use App\Enums\MenuLocation;
use App\Enums\RecordStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Support\SiteRoutes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Website menus (header, footer): groups and links, ordered by drag and drop.
 */
class MenuController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:menus.view', only: ['index']),
            new Middleware('can:menus.edit', only: ['toggleStatus', 'reorder']),
            new Middleware('can:menus.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request): View
    {
        $location = $request->enum('menu', MenuLocation::class) ?? MenuLocation::Header;
        $menus = collect(MenuLocation::cases())->mapWithKeys(fn (MenuLocation $l) => [$l->value => Menu::forLocation($l)]);
        $menu = $menus[$location->value];
        $items = $menu->tree()->get();

        return view('admin.menus.index', [
            'menus' => $menus,
            'menu' => $menu,
            'items' => $items,
            'groups' => $items->filter->isGroup()->mapWithKeys(fn (MenuItem $group) => [$group->id => $group->label])->all(),
            'routes' => SiteRoutes::options(),
            'pages' => Page::orderBy('title')->get(['id', 'title', 'status'])
                ->mapWithKeys(fn (Page $page) => [$page->id => $page->title.($page->status->value === 'Published' ? '' : ' (draft)')])->all(),
        ]);
    }

    public function store(MenuItemRequest $request, Menu $menu): RedirectResponse
    {
        $data = $request->itemData();
        $item = $menu->items()->create($data + ['sort_order' => $this->nextPosition($menu, $data['parent_id'])]);
        activity()->log('Menus', "Added {$item->link_type->shortLabel()} to {$menu->name}", $item, $data);

        return $this->backTo($menu, "“{$item->label}” added to the {$menu->name}.");
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $request->itemData();

        if ($data['parent_id'] !== $menuItem->parent_id) {
            $data['sort_order'] = $this->nextPosition($menuItem->menu, $data['parent_id']);
        }

        $menuItem->update($data);
        activity()->log('Menus', 'Updated menu item', $menuItem, $data);

        return $this->backTo($menuItem->menu, "“{$menuItem->label}” updated.");
    }

    public function toggleStatus(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['status' => $menuItem->isActive() ? RecordStatus::Inactive : RecordStatus::Active]);
        activity()->log('Menus', 'Changed menu item status to '.$menuItem->status->value, $menuItem);

        return $this->backTo($menuItem->menu, $menuItem->isActive() ? "“{$menuItem->label}” is now shown on the website." : "“{$menuItem->label}” is now hidden from the website.");
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menu = $menuItem->menu;
        $links = $menuItem->children()->count();
        activity()->log('Menus', 'Deleted menu item', $menuItem, ['label' => $menuItem->label, 'links_removed' => $links]);
        $menuItem->delete();

        return $this->backTo($menu, $links ? "Group “{$menuItem->label}” and its {$links} link(s) deleted." : "“{$menuItem->label}” deleted.");
    }

    /**
     * Saves the drag-and-drop order: [{id, parent_id, sort_order}, …] for every item of the menu.
     */
    public function reorder(Request $request, Menu $menu): JsonResponse
    {
        $rows = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.parent_id' => ['nullable', 'integer'],
            'items.*.sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ])['items'];

        $existing = $menu->items()->get()->keyBy('id');
        $groupIds = $existing->filter->isGroup()->keys();

        foreach ($rows as $row) {
            $item = $existing->get($row['id']);
            $parentId = $row['parent_id'] ?? null;

            if (! $item || ($parentId !== null && (! $groupIds->contains($parentId) || $item->isGroup()))) {
                throw ValidationException::withMessages(['items' => 'Groups stay at the top level and links can only be placed inside a group.']);
            }
        }

        DB::transaction(function () use ($rows, $existing) {
            foreach ($rows as $row) {
                $existing[$row['id']]->update(['parent_id' => $row['parent_id'] ?? null, 'sort_order' => $row['sort_order']]);
            }
        });

        activity()->log('Menus', "Reordered {$menu->name}", $menu);

        return response()->json(['message' => 'Menu order saved.']);
    }

    private function nextPosition(Menu $menu, ?int $parentId): int
    {
        return (int) $menu->items()->where('parent_id', $parentId)->max('sort_order') + 1;
    }

    private function backTo(Menu $menu, string $message): RedirectResponse
    {
        return redirect()->route('admin.menus.index', ['menu' => $menu->location->value])->with('success', $message);
    }
}
