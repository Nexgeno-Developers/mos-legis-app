<?php

namespace Tests\Feature\Site;

use App\Models\Page;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\PageSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Section headings and the Content field of template pages come from Admin → Pages.
 */
class PageHeadingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CatalogueSeeder::class, PageSeeder::class]);
    }

    private function save(Page $page, array $meta, array $extra = []): void
    {
        $this->actingAs($this->superadmin())->put(route('admin.pages.update', $page), [
            'title' => $page->title, 'slug' => $page->slug, 'status' => 'Published', 'meta' => $meta,
        ] + $extra)->assertSessionHasNoErrors();
    }

    #[Test]
    public function best_paper_headings_and_content_are_managed_in_the_admin(): void
    {
        $page = Page::where('template', 'paper_winner')->firstOrFail();
        $this->get(page_url('paper_winner'))->assertSee('How Winners Are Chosen')->assertSee('Past Winners'); // seeded wording

        $this->save($page, [
            'winner_choose_title' => 'Selection', 'winner_choose_desc' => 'By vote.',
            'prize_title' => '', 'prize_desc' => '', 'past_heading' => 'Hall of fame',
        ], ['content' => '<p>Extra award notes</p>']);

        $this->get(page_url('paper_winner'))->assertOk()
            ->assertSee('Selection')->assertSee('By vote.')->assertSee('Hall of fame')->assertSee('Extra award notes')
            ->assertDontSee('How Winners Are Chosen')->assertDontSee('The Prize')->assertDontSee('Past Winners');
    }

    #[Test]
    public function contact_careers_and_patrons_headings_are_editable(): void
    {
        $this->save(Page::where('template', 'contact')->firstOrFail(), ['form_heading' => 'Drop us a line', 'contacts_heading' => ''], ['content' => '<p>Office closed on Sundays</p>']);
        $this->get(page_url('contact'))->assertSee('Drop us a line')->assertSee('Office closed on Sundays')
            ->assertDontSee('Send us a message')->assertDontSee('Editorial contacts');

        $this->save(Page::where('template', 'career')->firstOrFail(), ['form_heading' => 'Apply here']);
        $this->get(page_url('career'))->assertSee('Apply here')->assertDontSee('Application Form');

        $patrons = Page::where('template', 'patron')->firstOrFail();
        $this->save($patrons, ['list_heading' => 'Our supporters']);
        $this->get('/'.$patrons->slug)->assertSee('Our supporters')->assertDontSee('With Gratitude')->assertDontSee('>Acknowledgements<', false);
    }
}
