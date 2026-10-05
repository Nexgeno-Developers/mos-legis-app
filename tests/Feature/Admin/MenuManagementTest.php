<?php

namespace Tests\Feature\Admin;

use App\Enums\MenuLinkType;
use App\Enums\MenuLocation;
use App\Enums\RecordStatus;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Database\Seeders\MenuSeeder;
use Database\Seeders\PageSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Default menus link to the CMS pages, so build them once the pages exist (as on a real site).
        MenuItem::query()->delete();
        $this->seed([PageSeeder::class, MenuSeeder::class]);
    }

    private function header(): Menu
    {
        return Menu::forLocation(MenuLocation::Header);
    }

    private function group(Menu $menu, string $label): MenuItem
    {
        return $menu->items()->where('label', $label)->where('link_type', MenuLinkType::None)->firstOrFail();
    }

    #[Test]
    public function default_menus_drive_the_website_header_and_footer(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeInOrder(['Home', 'Journal', 'About the Journal', 'Editorial Board', 'Patrons', 'Submit'])
            ->assertSee('Quick Links');
    }

    #[Test]
    public function menu_builder_renders_for_each_location(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('admin.menus.index'))->assertOk()->assertSee('Journal')->assertSee('Header menu');
        $this->actingAs($admin)->get(route('admin.menus.index', ['menu' => 'footer']))->assertOk()->assertSee('Quick Links');
    }

    #[Test]
    public function a_link_added_to_a_group_appears_in_the_header_dropdown(): void
    {
        $menu = $this->header();
        $journal = $this->group($menu, 'Journal');

        $this->actingAs($this->superadmin())->post(route('admin.menus.items.store', $menu), [
            'label' => 'Submission Guidelines',
            'link_type' => 'url',
            'url' => '/policies/submission-guidelines',
            'parent_id' => $journal->id,
            'open_in_new_tab' => '1',
            'status' => 'Active',
        ])->assertRedirect(route('admin.menus.index', ['menu' => 'header']))->assertSessionHasNoErrors();

        $item = MenuItem::where('label', 'Submission Guidelines')->firstOrFail();
        $this->assertSame($journal->id, $item->parent_id);
        $this->assertSame($journal->children()->max('sort_order'), $item->sort_order);

        $this->get(route('home'))->assertSeeInOrder(['Journal', 'Patrons', 'Submission Guidelines'])->assertSee('target="_blank"', false);
    }

    #[Test]
    public function fields_for_the_chosen_link_type_are_required_and_others_are_cleared(): void
    {
        $menu = $this->header();
        $admin = $this->superadmin();

        $this->actingAs($admin)->post(route('admin.menus.items.store', $menu), ['label' => 'X', 'link_type' => 'route', 'status' => 'Active'])
            ->assertSessionHasErrors('route_name');
        $this->actingAs($admin)->post(route('admin.menus.items.store', $menu), ['label' => 'X', 'link_type' => 'url', 'url' => 'javascript:alert(1)', 'status' => 'Active'])
            ->assertSessionHasErrors('url');

        $this->actingAs($admin)->post(route('admin.menus.items.store', $menu), [
            'label' => 'Resources', 'link_type' => 'none', 'route_name' => 'home', 'url' => 'https://x.test', 'open_in_new_tab' => '1', 'status' => 'Active',
        ])->assertSessionHasNoErrors();

        $group = MenuItem::where('label', 'Resources')->firstOrFail();
        $this->assertNull($group->route_name);
        $this->assertNull($group->url);
        $this->assertFalse($group->open_in_new_tab);
    }

    #[Test]
    public function groups_cannot_be_nested_and_a_group_with_links_keeps_its_type(): void
    {
        $menu = $this->header();
        $admin = $this->superadmin();
        $journal = $this->group($menu, 'Journal');

        $this->actingAs($admin)->post(route('admin.menus.items.store', $menu), [
            'label' => 'Nested', 'link_type' => 'none', 'parent_id' => $journal->id, 'status' => 'Active',
        ])->assertSessionHasErrors('parent_id');

        $this->actingAs($admin)->put(route('admin.menus.items.update', $journal), [
            'label' => 'Journal', 'link_type' => 'route', 'route_name' => 'about', 'status' => 'Active',
        ])->assertSessionHasErrors('link_type');

        $link = $menu->items()->where('label', 'Submit')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.menus.items.store', $menu), [
            'label' => 'Bad parent', 'link_type' => 'route', 'route_name' => 'home', 'parent_id' => $link->id, 'status' => 'Active',
        ])->assertSessionHasErrors('parent_id');
    }

    #[Test]
    public function drag_and_drop_order_is_saved_including_moving_a_link_into_a_group(): void
    {
        $menu = $this->header();
        $journal = $this->group($menu, 'Journal');
        $contact = $menu->items()->where('label', 'Contact')->firstOrFail();

        $rows = $menu->items()->get()->map(fn (MenuItem $item) => [
            'id' => $item->id,
            'parent_id' => $item->id === $contact->id ? $journal->id : $item->parent_id,
            'sort_order' => $item->id === $contact->id ? 0 : $item->sort_order + 1,
        ])->all();

        $this->actingAs($this->superadmin())->patchJson(route('admin.menus.reorder', $menu), ['items' => $rows])->assertOk();

        $this->assertSame($journal->id, $contact->fresh()->parent_id);
        $this->get(route('home'))->assertSeeInOrder(['Journal', 'Contact', 'About the Journal']);
    }

    #[Test]
    public function reorder_rejects_a_group_inside_a_group_and_items_from_another_menu(): void
    {
        $menu = $this->header();
        $journal = $this->group($menu, 'Journal');
        $jobs = $this->group($menu, 'Jobs');
        $admin = $this->superadmin();

        $this->actingAs($admin)->patchJson(route('admin.menus.reorder', $menu), ['items' => [['id' => $jobs->id, 'parent_id' => $journal->id, 'sort_order' => 0]]])
            ->assertUnprocessable();

        $footerItem = Menu::forLocation(MenuLocation::Footer)->items()->firstOrFail();
        $this->actingAs($admin)->patchJson(route('admin.menus.reorder', $menu), ['items' => [['id' => $footerItem->id, 'parent_id' => null, 'sort_order' => 0]]])
            ->assertUnprocessable();

        $this->assertNull($jobs->fresh()->parent_id);
    }

    #[Test]
    public function hidden_items_draft_pages_and_empty_groups_are_left_off_the_website(): void
    {
        $menu = $this->header();
        $admin = $this->superadmin();
        $draft = Page::factory()->create(['title' => 'Draft Policy', 'slug' => 'draft-policy', 'status' => 'Draft', 'template' => 'layout']);

        $menu->items()->create(['label' => 'Draft Policy Link', 'link_type' => MenuLinkType::Page, 'page_id' => $draft->id, 'sort_order' => 99]);
        $menu->items()->create(['label' => 'Empty Group', 'link_type' => MenuLinkType::None, 'sort_order' => 100]);
        $blogs = $menu->items()->where('label', 'Blogs')->firstOrFail();
        $this->actingAs($admin)->patch(route('admin.menus.items.toggle-status', $blogs))->assertRedirect();
        $this->assertSame(RecordStatus::Inactive, $blogs->fresh()->status);

        $this->get(route('home'))->assertDontSee('Draft Policy Link')->assertDontSee('Empty Group')->assertDontSee('>Blogs<', false);

        $draft->update(['status' => 'Published']);
        $this->get(route('home'))->assertSee('Draft Policy Link');
    }

    #[Test]
    public function deleting_a_group_removes_its_links(): void
    {
        $menu = $this->header();
        $jobs = $this->group($menu, 'Jobs');

        $this->actingAs($this->superadmin())->delete(route('admin.menus.items.destroy', $jobs))->assertRedirect();

        $this->assertSame(0, $menu->items()->whereIn('label', ['Jobs', 'Job Board', 'Careers at MOS Legis'])->count());
        $this->get(route('home'))->assertDontSee('Job Board');
    }

    #[Test]
    public function menus_require_the_menus_permissions(): void
    {
        $menu = $this->header();
        $item = $menu->items()->firstOrFail();

        $this->actingAs($this->reviewer())->get(route('admin.menus.index'))->assertForbidden();
        $this->actingAs($this->reviewer(['menus.view']))->patch(route('admin.menus.items.toggle-status', $item))->assertForbidden();
        $this->actingAs($this->reviewer(['menus.view']))->post(route('admin.menus.items.store', $menu), [
            'label' => 'X', 'link_type' => 'route', 'route_name' => 'home', 'status' => 'Active',
        ])->assertForbidden();
    }
}
