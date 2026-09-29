<?php

namespace Tests\Feature\Admin;

use App\Enums\PageTemplate;
use App\Models\Page;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PageManagementTest extends TestCase
{
    #[Test]
    public function pages_list_and_editors_render_for_every_template(): void
    {
        $admin = $this->superadmin();
        $this->actingAs($admin)->get(route('admin.pages.index'))->assertOk();

        foreach (PageTemplate::cases() as $template) {
            $this->actingAs($admin)->get(route('admin.pages.create', ['template' => $template->value]))->assertOk();
        }
    }

    #[Test]
    public function layout_page_is_created_with_generated_slug_and_sanitized_content(): void
    {
        $this->actingAs($this->superadmin())->post(route('admin.pages.store'), [
            'title' => 'Aim & Scope',
            'template' => 'layout',
            'status' => 'Published',
            'content' => '<p>Scope</p><script>alert(1)</script><a href="javascript:alert(1)">x</a>',
        ])->assertSessionHasNoErrors();

        $page = Page::where('slug', 'aim-scope')->firstOrFail();
        $this->assertStringNotContainsString('<script', $page->content);
        $this->assertStringNotContainsString('javascript:', $page->content);
        $this->assertStringContainsString('<p>Scope</p>', $page->content);
    }

    #[Test]
    public function patron_entries_are_stored_as_one_json_meta(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->post(route('admin.pages.store'), [
            'title' => 'Patron Acknowledgements',
            'slug' => 'patrons',
            'template' => 'patron',
            'status' => 'Published',
            'meta' => ['entries' => [
                ['description' => 'Justice (Retd.) R. N. Ganguly', 'month_year' => 'January 2026'],
                ['description' => '', 'month_year' => ''],
            ]],
        ])->assertSessionHasNoErrors();

        $page = Page::where('slug', 'patrons')->firstOrFail()->load('metas');
        $this->assertSame([['description' => 'Justice (Retd.) R. N. Ganguly', 'month_year' => 'January 2026']], $page->meta('entries'));
        $this->assertSame(1, $page->metas()->where('meta_key', 'entries')->count());
    }

    #[Test]
    public function contact_page_metas_are_updated(): void
    {
        $admin = $this->superadmin();
        $page = Page::factory()->create(['template' => PageTemplate::Contact, 'slug' => 'contact']);

        $this->actingAs($admin)->put(route('admin.pages.update', $page), [
            'title' => 'Contact Us',
            'slug' => 'contact',
            'status' => 'Published',
            'meta' => [
                'chief_editor_email' => 'chief@moslegis.com',
                'telephone' => '+91 22 4021 5588',
                'faqs' => [['question' => 'Refundable?', 'answer' => 'No.']],
            ],
        ])->assertSessionHasNoErrors();

        $page->load('metas');
        $this->assertSame('chief@moslegis.com', $page->meta('chief_editor_email'));
        $this->assertSame('Refundable?', $page->meta('faqs')[0]['question']);
    }

    #[Test]
    public function template_cannot_be_changed_after_creation(): void
    {
        $page = Page::factory()->create(['template' => PageTemplate::Layout]);

        $this->actingAs($this->superadmin())->put(route('admin.pages.update', $page), [
            'title' => $page->title, 'slug' => $page->slug, 'status' => 'Published', 'template' => 'contact',
        ])->assertSessionHasErrors('template');
    }

    #[Test]
    public function page_can_be_duplicated_toggled_and_deleted(): void
    {
        $admin = $this->superadmin();
        $page = Page::factory()->create(['slug' => 'disclaimer']);
        $page->metas()->create(['meta_key' => 'note', 'meta_value' => 'x', 'meta_type' => 'string']);

        $this->actingAs($admin)->post(route('admin.pages.duplicate', $page))->assertRedirect();
        $copy = Page::where('slug', 'disclaimer-copy')->firstOrFail();
        $this->assertSame('Draft', $copy->status->value);
        $this->assertSame(1, $copy->metas()->count());

        $this->actingAs($admin)->patch(route('admin.pages.toggle-status', $page));
        $this->assertSame('Draft', $page->fresh()->status->value);

        $this->actingAs($admin)->delete(route('admin.pages.destroy', $copy));
        $this->assertModelMissing($copy);
    }

    #[Test]
    public function reviewer_without_permission_cannot_edit_pages(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->reviewer())->get(route('admin.pages.edit', $page))->assertForbidden();
    }
}
