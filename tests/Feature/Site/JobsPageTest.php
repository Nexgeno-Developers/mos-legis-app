<?php

namespace Tests\Feature\Site;

use App\Models\JobPosting;
use App\Models\Page;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Job Postings: title, intro, breadcrumb, content and SEO from Admin → Pages; filters and listings stay dynamic.
 */
class JobsPageTest extends TestCase
{
    /** The Job Postings page created by the migration, with test content. */
    private function jobsPage(array $attributes = []): Page
    {
        $page = Page::where('template', 'jobs')->firstOrFail();
        $page->update($attributes + [
            'title' => 'Legal Careers Board', 'slug' => 'job-postings', 'template' => 'jobs', 'status' => 'Published',
            'excerpt' => 'Openings from firms and chambers.', 'content' => '<p>Listings are verified weekly.</p>',
            'seo_title' => 'Legal Jobs in India', 'seo_description' => 'Browse legal vacancies.',
        ]);

        return $page;
    }

    #[Test]
    public function the_page_text_and_meta_come_from_the_cms_and_listings_stay_dynamic(): void
    {
        $this->jobsPage();
        JobPosting::factory()->create(['job_title' => 'Litigation Associate']);

        $this->get(route('jobs.index'))->assertOk()
            ->assertSee('<title>Legal Jobs in India | MOS Legis</title>', false)
            ->assertSee('<meta name="description" content="Browse legal vacancies.">', false)
            ->assertSeeInOrder(['Home', 'Legal Careers Board', 'Openings from firms and chambers.'])
            ->assertSee('Litigation Associate')
            ->assertSee('Listings are verified weekly.');

        // Filters still work.
        $this->get(route('jobs.index', ['search' => 'nothing-matches']))->assertOk()->assertDontSee('Litigation Associate');
    }

    #[Test]
    public function a_draft_jobs_page_hides_the_job_board(): void
    {
        $this->jobsPage(['status' => 'Draft']);

        $this->get(route('jobs.index'))->assertNotFound();
    }

    #[Test]
    public function the_board_still_works_with_default_text_before_the_page_exists(): void
    {
        Page::where('template', 'jobs')->delete();

        $this->get(route('jobs.index'))->assertOk()->assertSee('Job Postings');
    }

    #[Test]
    public function admins_edit_the_jobs_page_like_any_other_page(): void
    {
        $page = $this->jobsPage();
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('admin.pages.edit', $page))->assertOk()->assertSee('Job postings');
        $this->actingAs($admin)->put(route('admin.pages.update', $page), [
            'title' => 'Vacancies', 'slug' => 'job-postings', 'status' => 'Published', 'excerpt' => 'New intro.',
        ])->assertSessionHasNoErrors();

        $this->get(route('jobs.index'))->assertSee('Vacancies')->assertSee('New intro.');
    }
}
