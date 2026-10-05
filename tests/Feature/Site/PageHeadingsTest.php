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
    public function best_paper_has_fixed_headings_and_shows_its_content_last(): void
    {
        $page = Page::where('template', 'paper_winner')->firstOrFail();
        $this->get(page_url('paper_winner'))->assertSee('Past Winners')->assertDontSee('The Award') // no info boxes
            ->assertSee('<h2>How Winners Are Chosen</h2>', false); // seeded as page content

        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $page))->assertOk()
            ->assertDontSee('Paper winner fields')->assertDontSee('Past winners heading'); // headings are fixed

        $this->save($page, [], ['content' => '<p>Extra award notes</p>']);

        $html = $this->get(page_url('paper_winner'))->assertOk()
            ->assertSee('Current Winner')->assertSee('Past Winners')->assertSee('Extra award notes')->getContent();
        // The page content comes last, after the past winners.
        $this->assertGreaterThan(strpos($html, 'Past Winners'), strpos($html, 'Extra award notes'));
    }

    #[Test]
    public function past_winners_have_the_standard_filter_bar_with_reset(): void
    {
        $this->get(page_url('paper_winner'))->assertOk()->assertDontSee('>Reset<', false);
        $this->get(page_url('paper_winner').'?year=2026')->assertOk()->assertSee('Reset');
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
