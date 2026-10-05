<?php

namespace Tests\Feature\Site;

use App\Models\Page;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\PageSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The home page has its own "Home" template: every piece of wording is managed in Admin → Pages → Home.
 */
class HomePageContentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CatalogueSeeder::class, PageSeeder::class]);
    }

    private function home(): Page
    {
        return Page::where('slug', 'home')->firstOrFail();
    }

    #[Test]
    public function the_home_page_uses_the_home_template_with_its_wording_stored(): void
    {
        $home = $this->home();
        $this->assertSame('home', $home->template->value);

        $this->get(route('home'))->assertOk()
            ->assertSee('Rooted in Tradition.')->assertSee('Why publish with us')->assertSee('Submit your manuscript today')
            ->assertSee(money(settings()->float('manuscript.plagiarism_prescreening_fee')).' pre-screening'); // {fee} filled in

        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $home))->assertOk()
            ->assertSee('Home fields')->assertSee('Hero — heading')->assertSee('Why publish — points');
    }

    #[Test]
    public function admins_edit_the_wording_and_empty_fields_hide_their_element(): void
    {
        $home = $this->home();

        $this->actingAs($this->superadmin())->put(route('admin.pages.update', $home), [
            'title' => $home->title, 'slug' => 'home', 'status' => 'Published',
            'meta' => [
                'hero_heading' => "Justice in writing\nSince 2026",
                'features_heading' => 'Why authors choose us',
                'features' => [['title' => 'Fast decisions', 'text' => 'Similarity limit {threshold}%']],
                'steps' => [],
                'cta_heading' => '', 'cta_text' => '', 'cta_primary' => '', 'cta_secondary' => '',
            ],
        ])->assertSessionHasNoErrors();

        $this->get(route('home'))->assertOk()
            ->assertSee('Justice in writing<br />', false)->assertSee('Why authors choose us')->assertSee('Fast decisions')
            ->assertSee('Similarity limit 10%')
            ->assertDontSee('Rooted in Tradition.<br', false)->assertDontSee('From submission to publication') // no steps → section hidden
            ->assertDontSee('Submit your manuscript today'); // closing band hidden
    }

    #[Test]
    public function home_and_archive_templates_cannot_be_created_again(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('admin.pages.create'))->assertOk()
            ->assertDontSee('value="home"', false)->assertDontSee('value="archive"', false);
        $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'Second home', 'template' => 'home', 'status' => 'Draft',
        ])->assertSessionHasErrors('template');
    }
}
