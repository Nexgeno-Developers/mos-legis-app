<?php

namespace Tests\Feature\Site;

use App\Enums\MenuLinkType;
use App\Enums\MenuLocation;
use App\Enums\PublishStatus;
use App\Models\Menu;
use App\Models\Page;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\PageSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Every CMS page lives at /{slug} and follows the slug and status set in Admin → Pages; only Home is fixed.
 */
class FlexiblePageSlugsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CatalogueSeeder::class, PageSeeder::class]);
    }

    private function save(Page $page, array $changes)
    {
        return $this->actingAs($this->superadmin())->put(route('admin.pages.update', $page), array_merge([
            'title' => $page->title, 'slug' => $page->slug, 'status' => $page->status->value,
        ], $changes));
    }

    #[Test]
    public function a_new_slug_takes_effect_immediately_and_the_old_one_is_gone(): void
    {
        $careers = Page::where('template', 'career')->first();
        $this->save($careers, ['slug' => 'work-with-us'])->assertSessionHasNoErrors();

        $this->get('/work-with-us')->assertOk()->assertSee($careers->title);
        $this->get('/careers')->assertNotFound();
        $this->assertSame(url('work-with-us'), page_url('career'));

        $about = Page::where('slug', 'about')->first();
        $this->save($about, ['slug' => 'about-the-journal']);
        $this->get('/about-the-journal')->assertOk()->assertSee($about->title);
    }

    #[Test]
    public function draft_pages_are_hidden_and_come_back_when_published(): void
    {
        $submit = Page::where('template', 'submit')->first();

        $this->save($submit, ['status' => PublishStatus::Draft->value]);
        $this->get('/submit')->assertNotFound();

        $this->save($submit->fresh(), ['status' => PublishStatus::Published->value]);
        $this->get('/submit')->assertOk();
    }

    #[Test]
    public function menu_links_follow_the_page(): void
    {
        $page = Page::where('template', 'career')->first();
        Menu::forLocation(MenuLocation::Header)->items()->create(['label' => 'Careers', 'link_type' => MenuLinkType::Page, 'page_id' => $page->id, 'sort_order' => 1]);

        $this->save($page, ['slug' => 'work-with-us']);
        $this->get('/')->assertSee(url('work-with-us'), false);

        $this->save($page->fresh(), ['status' => PublishStatus::Draft->value]);
        $this->get('/')->assertDontSee(url('work-with-us'), false);
    }

    #[Test]
    public function home_keeps_its_slug_and_status(): void
    {
        $home = Page::where('slug', 'home')->first();
        $this->save($home, ['slug' => 'start', 'status' => PublishStatus::Draft->value])->assertSessionHasNoErrors();

        $home->refresh();
        $this->assertSame('home', $home->slug);
        $this->assertSame(PublishStatus::Published, $home->status);
        $this->actingAs($this->superadmin())->delete(route('admin.pages.destroy', $home));
        $this->assertModelExists($home);
        $this->get('/home')->assertNotFound();
    }

    #[Test]
    public function slugs_used_by_fixed_site_addresses_are_refused(): void
    {
        $page = Page::where('slug', 'about')->first();

        $this->save($page, ['slug' => 'blogs'])->assertSessionHasErrors('slug');
        $this->save($page, ['slug' => 'admin'])->assertSessionHasErrors('slug');
        // A POST-only address (the contact form) does not block the slug.
        $this->save(Page::where('template', 'contact')->first(), ['slug' => 'contact'])->assertSessionHasNoErrors();
    }

    #[Test]
    public function submit_plagiarism_and_contact_keep_their_fixed_addresses(): void
    {
        foreach (['submit' => 'submit', 'plagiarism_checker' => 'plagiarism-checker', 'contact' => 'contact'] as $template => $slug) {
            $page = Page::where('template', $template)->firstOrFail();
            $this->save($page, ['slug' => 'renamed-'.$slug])->assertSessionHasNoErrors();
            $this->assertSame($slug, $page->fresh()->slug);
            $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $page))->assertSee('This address is fixed');
            $this->actingAs($this->superadmin())->delete(route('admin.pages.destroy', $page))->assertSessionHas('error');
        }
    }

    #[Test]
    public function old_policy_addresses_redirect(): void
    {
        $this->get('/policies/privacy-policy')->assertRedirect(url('privacy-policy'));
    }

    #[Test]
    public function the_editor_shows_the_current_page_address(): void
    {
        $jobs = Page::where('template', 'jobs')->first();
        $this->save($jobs, ['slug' => 'job-postings1']);

        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $jobs))->assertOk()
            ->assertSee('Page address:')->assertSee(url('/').'/', false)->assertSee('job-postings1</span>', false)
            ->assertSee('href="'.url('job-postings1').'"', false); // View on site

        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', Page::where('slug', 'home')->first()))
            ->assertSee('The home page always lives at the site address')->assertDontSee('Page address:');
    }
}
