<?php

namespace Tests\Feature\Author;

use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ManuscriptFee;
use App\Models\ManuscriptSubmission;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

/**
 * Author category, institution and country come from the author's profile, not the form.
 */
class SubmissionProfileDataTest extends TestCase
{
    private ContentCategory $content;

    private AuthorCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
        $this->seed([NotificationTemplateSeeder::class, PageSeeder::class]);
        config(['services.plagiarism.fake_similarity' => '4']);

        $this->content = ContentCategory::factory()->create(['min_word_limit' => 100, 'max_word_limit' => 5000]);
        $this->category = AuthorCategory::factory()->create();
        ManuscriptFee::create(['author_category_id' => $this->category->id, 'content_category_id' => $this->content->id, 'fees' => 2000]);
    }

    private function manuscript(array $extra = []): array
    {
        return [
            'title' => 'AI and Evidence Law', 'content_category_id' => $this->content->id, 'keywords' => 'ai, evidence, courts',
            'abstract' => 'Abstract.', 'manuscript' => Docx::withWords(900), 'co_authors' => ['R. Mehta'],
        ] + $extra + array_fill_keys(array_keys(ManuscriptSubmission::DECLARATIONS), '1');
    }

    private function authorWithProfile(array $profile = []): User
    {
        $author = User::factory()->author($this->category)->create();
        $author->authorProfile()->update($profile + ['institution' => 'NLSIU Bengaluru', 'country' => 'India']);

        return $author->fresh();
    }

    #[Test]
    public function the_submission_records_the_profile_details_whatever_the_form_sends(): void
    {
        $author = $this->authorWithProfile();
        $other = AuthorCategory::factory()->create();

        $this->actingAs($author)->post(route('account.submissions.store'), $this->manuscript([
            'author_category_id' => $other->id, 'institution' => 'Typed elsewhere', 'country' => 'Nowhere',
        ]))->assertSessionHasNoErrors();

        $submission = ManuscriptSubmission::latest('id')->firstOrFail();
        $this->assertSame($this->category->id, $submission->author_category_id);
        $this->assertSame('NLSIU Bengaluru', $submission->institution);
        $this->assertSame('India', $submission->country);
        $this->assertSame(['R. Mehta'], $submission->co_authors);

        // A later profile change does not rewrite the recorded submission.
        $author->authorProfile()->update(['institution' => 'Moved University']);
        $this->assertSame('NLSIU Bengaluru', $submission->fresh()->institution);
    }

    #[Test]
    public function the_form_has_three_steps_and_shows_who_is_submitting(): void
    {
        $author = $this->authorWithProfile();

        $this->actingAs($author)->get(page_url('submit'))->assertOk()
            ->assertSee('Submitting as')->assertSee('NLSIU Bengaluru')
            ->assertSeeInOrder(['Step 1', 'Manuscript', 'Step 2', 'Declarations', 'Step 3', 'Payment &amp; submit'], false)
            ->assertDontSee('Step 4'); // no separate "Author details" step (the CMS copy may mention author details)
    }

    #[Test]
    public function an_incomplete_profile_must_be_completed_before_submitting(): void
    {
        $author = $this->authorWithProfile(['institution' => null]);

        $this->actingAs($author)->get(page_url('submit'))->assertOk()
            ->assertSee('Complete your author profile first')->assertSee('add your institution')->assertDontSee('Submitting as');

        $this->actingAs($author)->post(route('account.submissions.store'), $this->manuscript())
            ->assertSessionHasErrors(['institution' => 'Your profile has no institution yet. Add it to the profile first.']);
    }

    #[Test]
    public function saving_the_profile_returns_to_the_submission_form(): void
    {
        $author = $this->authorWithProfile();
        $data = ['name' => $author->name, 'author_category_id' => $this->category->id, 'institution' => 'NLSIU'];

        $this->actingAs($author)->put(route('account.profile.update'), $data + ['next' => '/submit#submission-form'])
            ->assertRedirect('/submit#submission-form');

        // Only same-site paths are followed.
        $this->actingAs($author)->from(route('account.profile.edit'))->put(route('account.profile.update'), $data + ['next' => '//evil.example'])
            ->assertRedirect(route('account.profile.edit'));
    }

    #[Test]
    public function admin_submissions_use_the_chosen_authors_profile_and_keep_it_when_edited(): void
    {
        $author = $this->authorWithProfile();
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('admin.submissions.create'))->assertOk()->assertSee('taken from the author’s profile', false);

        $this->actingAs($admin)->post(route('admin.submissions.store'), $this->manuscript(['user_id' => $author->id]))->assertSessionHasNoErrors();
        $submission = ManuscriptSubmission::latest('id')->firstOrFail();
        $this->assertSame('NLSIU Bengaluru', $submission->institution);

        $author->authorProfile()->update(['institution' => 'Changed Later']);
        $this->actingAs($admin)->put(route('admin.submissions.update', $submission), [
            'title' => 'Edited title', 'content_category_id' => $this->content->id, 'keywords' => 'ai, evidence, courts', 'abstract' => 'Abstract.',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Edited title', $submission->fresh()->title);
        $this->assertSame('NLSIU Bengaluru', $submission->fresh()->institution);
        $this->actingAs($admin)->get(route('admin.submissions.edit', $submission))->assertOk()->assertSee('Recorded when the manuscript was submitted.');
    }
}
