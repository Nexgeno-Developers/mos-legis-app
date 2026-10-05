<?php

namespace Tests\Feature\Site;

use App\Models\Page;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\PageSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The Journal Archive's title, intro, content and SEO are managed in Admin → Pages; its address stays /archive.
 */
class ArchivePageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CatalogueSeeder::class, PageSeeder::class]);
    }

    private function page(): Page
    {
        return Page::where('template', 'archive')->firstOrFail();
    }

    #[Test]
    public function title_intro_seo_and_content_come_from_the_page(): void
    {
        $page = $this->page();
        $this->actingAs($this->superadmin())->put(route('admin.pages.update', $page), [
            'title' => 'Our Archive', 'slug' => 'something-else', 'status' => 'Published', 'excerpt' => 'All our scholarship.',
            'seo_title' => 'Archive of Legal Scholarship', 'seo_description' => 'Browse every paper.', 'content' => '<p>About the archive</p>',
        ])->assertSessionHasNoErrors();

        $this->assertSame('archive', $page->fresh()->slug); // fixed address
        $this->get(route('archive.index'))->assertOk()
            ->assertSee('Our Archive')->assertSee('All our scholarship.')->assertSee('About the archive')
            ->assertSee('<title>Archive of Legal Scholarship | MOS Legis</title>', false)
            ->assertDontSee('Every published manuscript, filterable');
    }

    #[Test]
    public function the_editor_locks_the_address_and_the_page_cannot_be_deleted(): void
    {
        $page = $this->page();
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('admin.pages.edit', $page))->assertOk()
            ->assertSee('This address is fixed')->assertSee('Published manuscripts, search, sort');
        $this->actingAs($admin)->delete(route('admin.pages.destroy', $page))->assertSessionHas('error');
        $this->assertModelExists($page);
    }

    #[Test]
    public function a_draft_archive_page_hides_the_archive(): void
    {
        $this->page()->update(['status' => 'Draft']);

        $this->get(route('archive.index'))->assertNotFound();
    }
}
