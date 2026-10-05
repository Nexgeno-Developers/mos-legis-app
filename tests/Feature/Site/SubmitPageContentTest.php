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
    public function default_wording_shows_until_the_page_is_saved_and_the_editor_is_prefilled(): void
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
}
