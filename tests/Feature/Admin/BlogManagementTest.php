<?php

namespace Tests\Feature\Admin;

use App\Enums\BlogStatus;
use App\Enums\CommentStatus;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\BlogTag;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogManagementTest extends TestCase
{
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'blog_title' => 'AI and the Courts',
            'category_id' => BlogCategory::factory()->create()->id,
            'author_name' => 'Editorial Board',
            'excerpt' => 'How AI tools are entering courtrooms.',
            'content' => '<p>Body</p><img src=x onerror=alert(1)>',
            'status' => 'Published',
            'publish_date' => today()->toDateString(),
            'featured_post' => '1',
        ], $overrides);
    }

    #[Test]
    public function admin_screens_render(): void
    {
        $admin = $this->superadmin();
        Blog::factory()->create(['blog_title' => 'Visible Post']);
        BlogComment::factory()->create(['comment' => 'Great read']);

        $this->actingAs($admin)->get(route('admin.blogs.index'))->assertOk()->assertSee('Visible Post');
        $this->actingAs($admin)->get(route('admin.blogs.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.blog-categories.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.blog-tags.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.blog-comments.index'))->assertOk()->assertSee('Great read');
    }

    #[Test]
    public function admin_creates_blog_with_tags_slug_and_sanitized_content(): void
    {
        $tags = BlogTag::factory()->count(2)->create();

        $this->actingAs($this->superadmin())
            ->post(route('admin.blogs.store'), $this->payload(['tag_ids' => $tags->pluck('id')->all()]))
            ->assertSessionHasNoErrors();

        $blog = Blog::firstOrFail();
        $this->assertSame('ai-and-the-courts', $blog->slug);
        $this->assertSame(BlogStatus::Published, $blog->status);
        $this->assertTrue($blog->featured_post);
        $this->assertCount(2, $blog->tags);
        $this->assertStringNotContainsString('onerror', $blog->content);
    }

    #[Test]
    public function duplicate_slugs_are_suffixed(): void
    {
        $admin = $this->superadmin();
        Blog::factory()->create(['slug' => 'ai-and-the-courts']);

        $this->actingAs($admin)->post(route('admin.blogs.store'), $this->payload())->assertSessionHasNoErrors();

        $this->assertDatabaseHas('blogs', ['slug' => 'ai-and-the-courts-2']);
    }

    #[Test]
    public function blog_can_be_toggled_duplicated_and_deleted(): void
    {
        $admin = $this->superadmin();
        $blog = Blog::factory()->create(['status' => BlogStatus::Published]);

        $this->actingAs($admin)->patch(route('admin.blogs.toggle-status', $blog));
        $this->assertSame(BlogStatus::Draft, $blog->fresh()->status);

        $this->actingAs($admin)->post(route('admin.blogs.duplicate', $blog))->assertRedirect();
        $this->assertSame(2, Blog::count());

        $this->actingAs($admin)->delete(route('admin.blogs.destroy', $blog));
        $this->assertModelMissing($blog);
    }

    #[Test]
    public function pending_author_post_is_approved_by_toggle(): void
    {
        $blog = Blog::factory()->create(['status' => BlogStatus::Pending]);

        $this->actingAs($this->superadmin())->patch(route('admin.blogs.toggle-status', $blog));

        $this->assertSame(BlogStatus::Published, $blog->fresh()->status);
    }

    #[Test]
    public function category_with_posts_cannot_be_deleted_and_can_be_duplicated(): void
    {
        $admin = $this->superadmin();
        $blog = Blog::factory()->create();

        $this->actingAs($admin)->delete(route('admin.blog-categories.destroy', $blog->category))->assertSessionHas('error');
        $this->actingAs($admin)->post(route('admin.blog-categories.duplicate', $blog->category))->assertSessionHas('success');

        $this->assertSame(2, BlogCategory::count());
    }

    #[Test]
    public function tag_slug_is_generated_and_unique(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->post(route('admin.blog-tags.store'), ['tag_name' => 'Data Protection', 'status' => 'Active'])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.blog-tags.store'), ['tag_name' => 'Data Protection', 'status' => 'Active'])->assertSessionHasErrors('slug');

        $this->assertDatabaseHas('blog_tags', ['slug' => 'data-protection', 'user_id' => $admin->id]);
    }

    #[Test]
    public function comments_can_be_approved_and_replied_to(): void
    {
        $admin = $this->superadmin();
        $comment = BlogComment::factory()->create(['status' => CommentStatus::Pending]);

        $this->actingAs($admin)->patch(route('admin.blog-comments.moderate', $comment), ['status' => 'Approved']);
        $comment->refresh();
        $this->assertSame(CommentStatus::Approved, $comment->status);
        $this->assertSame($admin->id, $comment->approved_by);

        $this->actingAs($admin)->post(route('admin.blog-comments.reply', $comment), ['comment' => 'Thank you!'])->assertSessionHas('success');

        $this->assertSame($admin->id, $comment->fresh()->replied_by);
        $this->assertDatabaseHas('blog_comments', ['parent_id' => $comment->id, 'comment' => 'Thank you!', 'status' => 'Approved']);
    }

    #[Test]
    public function reviewer_needs_permissions_for_blogs(): void
    {
        $reviewer = $this->reviewer();
        $blog = Blog::factory()->create();

        $this->actingAs($reviewer)->get(route('admin.blogs.index'))->assertForbidden();
        $this->actingAs($reviewer)->delete(route('admin.blogs.destroy', $blog))->assertForbidden();

        $editor = $this->reviewer(['blogs.view', 'blogs.edit']);
        $this->actingAs($editor)->get(route('admin.blogs.edit', $blog))->assertOk();
    }
}
