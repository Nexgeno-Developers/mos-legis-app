<?php

namespace Tests\Feature\Author;

use App\Enums\BlogStatus;
use App\Enums\ManuscriptStage;
use App\Enums\PaymentPurpose;
use App\Enums\RevisionDecision;
use App\Models\AuthorCategory;
use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\ContentCategory;
use App\Models\ContentCategoryTheme;
use App\Models\ManuscriptFee;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Models\PlagiarismCheck;
use App\Models\User;
use App\Services\Manuscripts\ManuscriptWorkflow;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

class PortalTest extends TestCase
{
    private User $author;

    private ContentCategory $content;

    private AuthorCategory $authorCategory;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Mail::fake();
        $this->seed(NotificationTemplateSeeder::class);
        config(['services.plagiarism.fake_similarity' => '4']);

        $this->content = ContentCategory::factory()->create(['min_word_limit' => 100, 'max_word_limit' => 5000]);
        $this->authorCategory = AuthorCategory::factory()->create();
        ManuscriptFee::create(['author_category_id' => $this->authorCategory->id, 'content_category_id' => $this->content->id, 'fees' => 2000]);
        $this->author = User::factory()->author($this->authorCategory)->create();
    }

    private function submitManuscript(): ManuscriptSubmission
    {
        $this->actingAs($this->author)->post(route('account.submissions.store'), [
            'author_category_id' => $this->authorCategory->id,
            'institution' => 'NLSIU',
            'country' => 'India',
            'title' => 'AI and Evidence Law',
            'content_category_id' => $this->content->id,
            'keywords' => 'ai, evidence, courts',
            'abstract' => 'Abstract.',
            'manuscript' => Docx::withWords(900),
        ] + array_fill_keys(array_keys(ManuscriptSubmission::DECLARATIONS), '1'))->assertSessionHasNoErrors();

        return ManuscriptSubmission::latest('id')->firstOrFail();
    }

    private function checkout(ManuscriptSubmission $submission, PaymentPurpose $purpose, string $country = 'IN'): Payment
    {
        $this->actingAs($this->author)->post(route('account.checkout.store'), [
            'payable_type' => 'manuscript_submissions',
            'payable_id' => $submission->id,
            'purpose' => $purpose->value,
            'recipient_name' => 'Ananya Iyer',
            'address_line1' => '1 Court Road',
            'city' => 'Mumbai',
            'country_code' => $country,
            'tax_id_type' => 'none',
        ])->assertRedirect();

        return Payment::latest('id')->firstOrFail();
    }

    #[Test]
    public function portal_pages_render(): void
    {
        foreach (['account.dashboard', 'account.submissions.index', 'account.payments.index', 'account.blogs.index', 'account.blogs.create', 'account.jobs.index', 'account.jobs.create', 'account.plagiarism-checks.index', 'account.profile.edit'] as $route) {
            $this->actingAs($this->author)->get(route($route))->assertOk();
        }
        $this->actingAs($this->author)->get(route('submit'))->assertOk()->assertSee('Manuscript Submission Form');
    }

    #[Test]
    public function author_submits_pays_and_the_manuscript_reaches_review_with_current_theme(): void
    {
        $theme = ContentCategoryTheme::factory()->create(['content_category_id' => $this->content->id, 'period' => now()->startOfMonth()]);
        $reviewer = $this->reviewer([], [$this->content->id]);

        $submission = $this->submitManuscript();
        $this->assertSame(900, $submission->word_count);
        $this->assertSame($theme->id, $submission->content_category_theme_id);
        $this->assertSame(ManuscriptStage::Pending, $submission->stage);

        $this->actingAs($this->author)->get(route('account.checkout.submission', [$submission, 'prescreening']))->assertOk();
        $payment = $this->checkout($submission, PaymentPurpose::Prescreening);
        $this->assertEquals(150.0, (float) $payment->amount);
        $this->assertEquals(27.0, (float) $payment->tax_amount);
        $this->assertSame('Mumbai', $payment->billing_details['city']);

        $this->actingAs($this->author)->get(route('account.payments.pay', $payment))->assertOk()->assertSee('Simulate successful payment');
        $this->actingAs($this->author)->post(route('account.payments.simulate', $payment))->assertRedirect(route('account.submissions.show', $submission));

        $submission->refresh();
        $this->assertSame(ManuscriptStage::InReview, $submission->stage);
        $this->assertSame($reviewer->id, $submission->assigned_to);
        $this->actingAs($this->author)->get(route('account.payments.invoice', $payment))->assertOk()->assertHeader('Content-Type', 'application/pdf');
    }

    #[Test]
    public function revision_upload_and_publication_payment(): void
    {
        $reviewer = $this->reviewer([], [$this->content->id]);
        $submission = $this->submitManuscript();
        $this->actingAs($this->author)->post(route('account.payments.simulate', $this->checkout($submission, PaymentPurpose::Prescreening)));
        $workflow = app(ManuscriptWorkflow::class);

        $workflow->decide($submission->fresh(), $reviewer, RevisionDecision::Revision, 'Expand part II.');
        $this->actingAs($this->author)->get(route('account.submissions.show', $submission))->assertOk()->assertSee('Expand part II.');

        $this->actingAs($this->author)->post(route('account.submissions.resubmit', $submission), [
            'manuscript' => Docx::withWords(1200), 'author_response' => 'Expanded.',
        ])->assertSessionHas('success');
        $this->assertSame(ManuscriptStage::Resubmitted, $submission->fresh()->stage);
        $this->assertSame(1200, $submission->fresh()->word_count);

        $workflow->decide($submission->fresh(), $reviewer, RevisionDecision::Approved, null);
        $payment = $this->checkout($submission->fresh(), PaymentPurpose::Publication, 'US');
        $this->assertEquals(2000.0, (float) $payment->amount);
        $this->assertEquals(0.0, (float) $payment->tax_amount);

        $this->actingAs($this->author)->post(route('account.payments.simulate', $payment));

        $this->assertSame(ManuscriptStage::Published, $submission->fresh()->stage);
        $this->actingAs($this->author)->get(route('account.submissions.certificate', $submission))->assertOk();
    }

    #[Test]
    public function fee_cannot_be_paid_out_of_stage_or_by_others(): void
    {
        $submission = $this->submitManuscript();

        $this->actingAs($this->author)->get(route('account.checkout.submission', [$submission, 'publication']))
            ->assertRedirect(route('account.submissions.show', $submission));

        $other = User::factory()->author($this->authorCategory)->create();
        $this->actingAs($other)->get(route('account.submissions.show', $submission))->assertForbidden();
        $this->actingAs($other)->get(route('account.checkout.submission', [$submission, 'prescreening']))->assertForbidden();
    }

    #[Test]
    public function uncombined_fee_matrix_cell_blocks_submission(): void
    {
        ManuscriptFee::query()->delete();

        $this->actingAs($this->author)->post(route('account.submissions.store'), [
            'author_category_id' => $this->authorCategory->id, 'institution' => 'X', 'country' => 'India',
            'title' => 'T', 'content_category_id' => $this->content->id, 'keywords' => 'a, b, c', 'abstract' => 'x',
            'manuscript' => Docx::withWords(500),
        ] + array_fill_keys(array_keys(ManuscriptSubmission::DECLARATIONS), '1'))->assertSessionHasErrors('content_category_id');
    }

    #[Test]
    public function author_blog_goes_to_pending_when_approval_is_required(): void
    {
        $category = BlogCategory::factory()->create();
        $payload = ['blog_title' => 'My Post', 'category_id' => $category->id, 'excerpt' => 'x', 'content' => '<p>Body</p>', 'status' => 'Published', 'publish_date' => today()->toDateString()];

        $this->actingAs($this->author)->post(route('account.blogs.store'), $payload)->assertRedirect(route('account.blogs.index'));
        $this->assertSame(BlogStatus::Pending, Blog::firstOrFail()->status);

        settings()->update(['general' => ['blog_author_approval_required' => '0']]);
        $this->actingAs($this->author)->post(route('account.blogs.store'), ['blog_title' => 'Second'] + $payload);
        $this->assertSame(BlogStatus::Published, Blog::where('blog_title', 'Second')->firstOrFail()->status);
    }

    #[Test]
    public function authors_cannot_edit_other_authors_blogs(): void
    {
        $blog = Blog::factory()->create();

        $this->actingAs($this->author)->get(route('account.blogs.edit', $blog))->assertForbidden();
    }

    #[Test]
    public function author_posts_a_job(): void
    {
        $this->actingAs($this->author)->post(route('account.jobs.store'), [
            'job_title' => 'Research Assistant', 'organisation' => 'NLU', 'location' => 'Delhi', 'work_mode' => 'Remote',
            'employment_type' => 'Contract', 'experience' => '0–1 years', 'practice_area' => 'Research', 'summary' => 's',
            'responsibilities' => 'r', 'qualifications' => 'q', 'required_skills' => 'k', 'application_method' => 'Email',
            'application_email_url' => 'jobs@nlu.ac.in', 'published_date' => today()->toDateString(),
            'application_deadline' => today()->addWeek()->toDateString(), 'expiry_date' => today()->addMonth()->toDateString(),
            'source_name' => 'NLU', 'source_url' => 'https://nlu.ac.in/jobs', 'status' => 'Active',
        ])->assertRedirect(route('account.jobs.index'));

        $this->assertDatabaseHas('job_postings', ['user_id' => $this->author->id, 'job_title' => 'Research Assistant']);
    }

    #[Test]
    public function standalone_plagiarism_check_flow(): void
    {
        $this->actingAs($this->author)->post(route('plagiarism-checker.store'), [
            'title' => 'Draft', 'content' => str_repeat('Federalism in India. ', 20),
        ])->assertRedirect();
        $check = PlagiarismCheck::firstOrFail();
        $this->assertNull($check->manuscript_submission_id);

        $this->actingAs($this->author)->post(route('account.checkout.store'), [
            'payable_type' => 'plagiarism_checks', 'payable_id' => $check->id, 'purpose' => 'plagiarism_check',
            'recipient_name' => 'A', 'address_line1' => 'x', 'city' => 'Pune', 'country_code' => 'IN', 'tax_id_type' => 'none',
        ]);
        $this->actingAs($this->author)->post(route('account.payments.simulate', Payment::latest('id')->first()));

        $check->refresh();
        $this->assertTrue($check->isCompleted());
        $this->assertEquals(4.0, (float) $check->similarity_percentage);
        $this->assertSame(0, ManuscriptSubmission::count());
        $this->actingAs($this->author)->get(route('account.plagiarism-checks.report', $check))->assertOk();
    }

    #[Test]
    public function profile_and_billing_address_are_saved(): void
    {
        $this->actingAs($this->author)->put(route('account.profile.update'), [
            'name' => 'Ananya', 'author_category_id' => $this->authorCategory->id, 'orcid' => '0000-0002-1825-0097', 'institution' => 'NLSIU',
        ])->assertSessionHasNoErrors();
        $this->assertSame('0000-0002-1825-0097', $this->author->fresh()->authorProfile->orcid);

        $this->actingAs($this->author)->put(route('account.profile.address'), [
            'recipient_name' => 'Ananya', 'address_line1' => '1 Road', 'city' => 'Pune', 'country_code' => 'IN', 'tax_id_type' => 'gst',
        ])->assertSessionHasErrors('tax_id_number');
    }
}
