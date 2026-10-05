<?php

namespace Tests\Feature\Site;

use App\Models\Page;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Plagiarism Checker: everything except the check form comes from Admin → Pages.
 */
class PlagiarismCheckerPageTest extends TestCase
{
    private function page(): Page
    {
        return Page::where('template', 'plagiarism_checker')->firstOrFail();
    }

    #[Test]
    public function admins_edit_the_text_steps_and_seo_and_placeholders_are_filled_in(): void
    {
        $page = $this->page();

        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $page))->assertOk()
            ->assertSee('Use {fee} for the checking fee');

        $this->actingAs($this->superadmin())->put(route('admin.pages.update', $page), [
            'title' => 'Similarity Check', 'slug' => $page->slug, 'status' => 'Published', 'excerpt' => 'Know your score first.',
            'seo_title' => 'Online Similarity Check', 'seo_description' => 'Check before you submit.',
            'meta' => [
                'steps_label' => 'Process', 'steps_heading' => 'Three simple steps',
                'steps' => [['text' => 'Pay {fee}.'], ['text' => 'Stay under {threshold}% to be considered.']],
                'steps_note' => 'Reports stay in your account.',
                'guest_heading' => 'Please sign in', 'guest_text' => 'Checks are linked to your account.',
            ],
        ])->assertSessionHasNoErrors();

        auth()->logout();
        $this->get(page_url('plagiarism_checker'))->assertOk()
            ->assertSee('<title>Online Similarity Check | MOS Legis</title>', false)
            ->assertSeeInOrder(['Similarity Check', 'Know your score first.', 'Please sign in', 'Process', 'Three simple steps'])
            ->assertSee('Pay '.money(app(\App\Services\Manuscripts\FeeCalculator::class)->standaloneCheckFee()).'.')
            ->assertDontSee('{fee}')->assertDontSee('{threshold}')
            ->assertSee('Reports stay in your account.');
    }

    #[Test]
    public function the_form_stays_for_authors(): void
    {
        $this->actingAs($this->author())->get(page_url('plagiarism_checker'))->assertOk()
            ->assertSee('Paste content')->assertSee('What happens after you pay');
    }

    #[Test]
    public function a_draft_page_hides_the_checker(): void
    {
        $this->page()->update(['status' => 'Draft']);

        $this->get(page_url('plagiarism_checker'))->assertNotFound();
    }

    #[Test]
    public function a_deleted_page_is_gone(): void
    {
        $this->page()->delete();

        $this->get('/plagiarism-checker')->assertNotFound();
    }
}
