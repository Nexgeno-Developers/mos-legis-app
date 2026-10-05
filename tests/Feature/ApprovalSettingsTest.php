<?php

namespace Tests\Feature;

use App\Enums\BlogStatus;
use App\Enums\CommentStatus;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogComment;
use App\Models\JobPosting;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Settings → Approvals: blog posts, blog comments and job postings, plus comment deletion
 * and the automatic publish date of author blog posts.
 */
class ApprovalSettingsTest extends TestCase
{
    private User $author;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seed([NotificationTemplateSeeder::class, PageSeeder::class]);
        $this->author = $this->author();
    }

    private function approvals(array $values): void
    {
        settings()->update(['approvals' => $values]);
    }

    #[Test]
    public function the_settings_screen_has_an_approvals_section(): void
    {
        $this->actingAs($this->superadmin())->get(route('admin.settings.edit'))->assertOk()
            ->assertSee('Approvals')->assertSee('Blog Posts Approval Required')
            ->assertSee('Blog Comment Approval Required')->assertSee('Job Posting Approval Required');
    }

    #[Test]
    public function comments_wait_for_approval_only_when_the_setting_is_on(): void
    {
        $blog = Blog::factory()->create(['status' => BlogStatus::Published]);

        $this->actingAs($this->author)->post(route('blogs.comments.store', $blog->slug), ['comment' => 'Needs a look']);
        $this->assertDatabaseHas('blog_comments', ['comment' => 'Needs a look', 'status' => 'Pending']);
        // The writer sees their own pending comment; others don't.
        $this->actingAs($this->author)->get(route('blogs.show', $blog->slug))->assertSee('Needs a look')->assertSee('Awaiting approval');
        $this->actingAs($this->author())->get(route('blogs.show', $blog->slug))->assertDontSee('Needs a look');

        $this->approvals(['blog_comment_approval_required' => '0']);
        $this->actingAs($this->author)->post(route('blogs.comments.store', $blog->slug), ['comment' => 'Straight in']);
        $this->assertDatabaseHas('blog_comments', ['comment' => 'Straight in', 'status' => 'Approved']);
        $this->get(route('blogs.show', $blog->slug))->assertSee('Straight in');
    }

    #[Test]
    public function writers_delete_their_own_comment_and_its_replies(): void
    {
        $blog = Blog::factory()->create(['status' => BlogStatus::Published]);
        $comment = BlogComment::factory()->create(['blog_id' => $blog->id, 'user_id' => $this->author->id, 'status' => CommentStatus::Approved]);
        $reply = BlogComment::factory()->create(['blog_id' => $blog->id, 'parent_id' => $comment->id, 'status' => CommentStatus::Approved]);

        $this->actingAs($this->author())->delete(route('blogs.comments.destroy', [$blog->slug, $comment]))->assertForbidden();

        $this->actingAs($this->author)->delete(route('blogs.comments.destroy', [$blog->slug, $comment]))->assertRedirect();
        $this->assertModelMissing($comment);
        $this->assertModelMissing($reply);
    }

    #[Test]
    public function author_posts_are_dated_automatically_and_approval_notifies_the_author(): void
    {
        $category = BlogCategory::factory()->create();
        $this->actingAs($this->author)->post(route('account.blogs.store'), [
            'blog_title' => 'Dated post', 'category_id' => $category->id, 'excerpt' => 'x', 'content' => '<p>Body</p>',
            'status' => 'Published', 'publish_date' => '2020-01-01',
        ])->assertSessionHasNoErrors();

        $blog = Blog::where('blog_title', 'Dated post')->firstOrFail();
        $this->assertSame(BlogStatus::Pending, $blog->status);
        $this->assertTrue($blog->publish_date->isToday());

        $this->actingAs($this->superadmin())->patch(route('admin.blogs.toggle-status', $blog));
        $this->assertSame(BlogStatus::Published, $blog->fresh()->status);
        $this->assertDatabaseHas('notification_logs', ['template_slug' => 'blog_approved', 'recipient' => $this->author->email]);
    }

    #[Test]
    public function author_jobs_are_listed_after_approval_when_required(): void
    {
        $job = JobPosting::factory()->create(['user_id' => $this->author->id, 'job_title' => 'Pending Clerk', 'approved_at' => null]);
        $this->get(page_url('jobs'))->assertDontSee('Pending Clerk');
        $this->actingAs($this->author)->get(route('account.jobs.index'))->assertSee('Awaiting approval');

        $this->actingAs($this->superadmin())->patch(route('admin.job-postings.approve', $job))->assertSessionHas('success');
        $this->assertNotNull($job->fresh()->approved_at);
        $this->get(page_url('jobs'))->assertSee('Pending Clerk');
    }

    #[Test]
    public function new_author_jobs_wait_for_approval_unless_it_is_switched_off(): void
    {
        $payload = [
            'job_title' => 'Research Assistant', 'organisation' => 'NLU', 'location' => 'Delhi', 'work_mode' => 'Remote',
            'employment_type' => 'Contract', 'experience' => '0–1 years', 'practice_area' => 'Research', 'summary' => 's',
            'responsibilities' => 'r', 'qualifications' => 'q', 'required_skills' => 'k', 'application_method' => 'Email',
            'application_email_url' => 'jobs@nlu.ac.in', 'application_deadline' => today()->addWeek()->toDateString(),
            'source_name' => 'NLU', 'source_url' => 'https://nlu.ac.in/jobs', 'status' => 'Active',
        ];

        $this->actingAs($this->author)->post(route('account.jobs.store'), $payload)->assertSessionHasNoErrors();
        $this->assertNull(JobPosting::where('job_title', 'Research Assistant')->value('approved_at'));

        $this->approvals(['job_author_approval_required' => '0']);
        $this->actingAs($this->author)->post(route('account.jobs.store'), ['job_title' => 'Live Assistant'] + $payload);
        $this->assertNotNull(JobPosting::where('job_title', 'Live Assistant')->value('approved_at'));
    }
}
