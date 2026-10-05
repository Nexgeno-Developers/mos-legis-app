<?php

namespace Tests\Feature\Admin;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Page;
use Database\Seeders\PageSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Admins get the full editor (formatting, tables, images); authors keep the simple one without attachments.
 */
class RichTextEditorTest extends TestCase
{
    private const FORMATTED = '<h2 style="text-align:center">Title</h2><p style="color:#b01b25" onclick="alert(1)">Hello</p><script>alert(1)</script><table border="1"><tbody><tr><td colspan="2">Cell</td></tr></tbody></table>';

    #[Test]
    public function admin_forms_use_the_full_editor_and_author_forms_the_simple_one(): void
    {
        $this->seed(PageSeeder::class);
        $page = Page::where('slug', 'about')->firstOrFail();

        $this->actingAs($this->superadmin())->get(route('admin.pages.edit', $page))->assertOk()
            ->assertSee('data-rich-editor', false)->assertSee(route('admin.editor-images.store'), false);
        $this->actingAs($this->superadmin())->get(route('admin.blogs.create'))->assertOk()->assertSee('data-rich-editor', false);
        $this->actingAs($this->author())->get(route('account.blogs.create'))->assertOk()
            ->assertSee('<trix-editor', false)->assertDontSee('data-rich-editor', false);
    }

    #[Test]
    public function admin_content_keeps_formatting_but_never_scripts(): void
    {
        $this->seed(PageSeeder::class);
        $page = Page::where('slug', 'about')->firstOrFail();

        $this->actingAs($this->superadmin())->put(route('admin.pages.update', $page), [
            'title' => $page->title, 'slug' => $page->slug, 'status' => 'Published', 'content' => self::FORMATTED,
        ])->assertSessionHasNoErrors();

        $content = $page->fresh()->content;
        $this->assertStringContainsString('style="text-align:center"', $content);
        $this->assertStringContainsString('colspan="2"', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('onclick', $content);
    }

    #[Test]
    public function author_blog_content_is_cleaned_strictly(): void
    {
        $category = BlogCategory::factory()->create();
        $this->actingAs($this->author())->post(route('account.blogs.store'), [
            'blog_title' => 'Styled', 'category_id' => $category->id, 'excerpt' => 'x', 'content' => self::FORMATTED, 'status' => BlogStatus::Draft->value,
        ])->assertSessionHasNoErrors();

        $content = Blog::where('blog_title', 'Styled')->value('content');
        $this->assertStringNotContainsString('style=', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringContainsString('Hello', $content);
    }

    #[Test]
    public function admins_upload_editor_images_and_bad_files_are_refused(): void
    {
        Storage::fake('public');
        $admin = $this->superadmin();

        $response = $this->actingAs($admin)->post(route('admin.editor-images.store'), [
            'files' => [UploadedFile::fake()->create('photo.png', 120, 'image/png')],
        ])->assertOk()->assertJson(['success' => true]);

        $file = $response->json('data.files.0');
        Storage::disk('public')->assertExists('editor/'.now()->format('Y/m').'/'.$file);
        $this->assertStringEndsWith('/editor/'.now()->format('Y/m').'/', $response->json('data.baseurl'));

        $this->actingAs($admin)->post(route('admin.editor-images.store'), [
            'files' => [UploadedFile::fake()->create('notes.pdf', 10, 'application/pdf')],
        ])->assertOk()->assertJson(['success' => false]);

        $this->actingAs($this->reviewer())->post(route('admin.editor-images.store'), [
            'files' => [UploadedFile::fake()->create('photo.png', 10, 'image/png')],
        ])->assertForbidden();
    }
}
