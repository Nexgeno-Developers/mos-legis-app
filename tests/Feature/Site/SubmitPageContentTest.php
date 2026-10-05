<?php

namespace Tests\Feature\Site;

use App\Models\Page;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\PageSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The wording around the Submit page's dynamic parts is managed in Admin → Pages → Submit.
 */
class SubmitPageContentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([CatalogueSeeder::class, PageSeeder::class]);
    }

    #[Test]
    public function the_seeded_wording_is_stored_on_the_page_and_shown_in_the_editor(): void
    {
        $page = Page::where('template', 'submit')->firstOrFail();

        $this->get('/submit')->assertOk()->assertSee('Everything you need before you submit')->assertSee('Submission Checklist');
        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $page))->assertOk()
            ->assertSee('Key facts (boxes above the form)')->assertSee('Everything you need before you submit');
    }

    #[Test]
    public function admins_edit_the_wording_and_empty_fields_are_hidden(): void
    {
        $page = Page::where('template', 'submit')->firstOrFail();

        $this->actingAs($this->superadmin())->put(route('admin.pages.update', $page), [
            'title' => $page->title, 'slug' => $page->slug, 'status' => 'Published',
            'meta' => [
                'facts' => [['title' => 'Only {fee} to start', 'text' => 'Reviewed within weeks']],
                'guide_heading' => 'Your submission guide',
                'steps' => [['title' => 'Upload', 'text' => 'Similarity limit {threshold}%']],
                'checklist' => [['text' => 'Anonymised file']],
                'categories_heading' => 'Pick a category',
                'cta_text' => '',
                'cta_button' => '',
            ],
        ])->assertSessionHasNoErrors();

        $this->get('/submit')->assertOk()
            ->assertSee('Only '.money(settings()->float('manuscript.plagiarism_prescreening_fee')).' to start')
            ->assertSee('Your submission guide')->assertSee('Similarity limit 10%')
            ->assertSee('Anonymised file')->assertSee('Pick a category')
            ->assertDontSee('Everything you need before you submit')
            ->assertDontSee('Ready to submit your manuscript?')
            ->assertDontSee('Submission Checklist'); // checklist heading saved empty
    }

    #[Test]
    public function pages_show_no_built_in_text_when_a_field_is_empty(): void
    {
        foreach (['submit', 'paper_winner', 'contact', 'plagiarism_checker', 'jobs', 'career'] as $template) {
            $page = Page::where('template', $template)->firstOrFail();
            $page->update(['excerpt' => null]);
            $this->get(page_url($template))->assertOk()->assertDontSee('measure mt-1.5 text-base leading-relaxed', false); // no intro line
        }

        $this->get(page_url('paper_winner'))->assertDontSee('Each month the Editorial Board selects one published manuscript');
    }

    #[Test]
    public function the_editor_lists_what_comes_from_other_modules(): void
    {
        $page = Page::where('template', 'submit')->firstOrFail();

        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $page))->assertOk()
            ->assertSee('Shown on this page automatically')
            ->assertSee('Fee table (author category × content category)')
            ->assertSee(route('admin.fees.index'), false)
            ->assertDontSee('Header and footer menus');
    }
}
