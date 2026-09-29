<?php

namespace Tests\Feature\Admin;

use App\Enums\ManuscriptStage;
use App\Models\AuthorCategory;
use App\Models\BestPaperAward;
use App\Models\ContentCategory;
use App\Models\ManuscriptFee;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Models\PlagiarismCheck;
use App\Models\User;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

class SubmissionManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
        $this->seed(NotificationTemplateSeeder::class);
    }

    #[Test]
    public function superadmin_sees_all_submissions_and_dashboard(): void
    {
        $admin = $this->superadmin();
        $submission = ManuscriptSubmission::factory()->create(['title' => 'Federalism and Fiscal Devolution']);

        $this->actingAs($admin)->get(route('admin.submissions.index'))->assertOk()->assertSee('Federalism and Fiscal Devolution');
        $this->actingAs($admin)->get(route('admin.submissions.index', ['search' => $submission->reference()]))->assertOk()->assertSee($submission->title);
        $this->actingAs($admin)->get(route('admin.submissions.show', $submission))->assertOk()->assertSee($submission->reference());
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Recent submissions');
    }

    #[Test]
    public function reviewer_only_sees_assigned_manuscripts_unless_granted_view_all(): void
    {
        $reviewer = $this->reviewer();
        $mine = ManuscriptSubmission::factory()->stage(ManuscriptStage::InReview, $reviewer)->create(['title' => 'Assigned To Me']);
        $other = ManuscriptSubmission::factory()->create(['title' => 'Someone Else']);

        $this->actingAs($reviewer)->get(route('admin.submissions.index'))->assertOk()->assertSee('Assigned To Me')->assertDontSee('Someone Else');
        $this->actingAs($reviewer)->get(route('admin.submissions.show', $mine))->assertOk();
        $this->actingAs($reviewer)->get(route('admin.submissions.show', $other))->assertForbidden();

        $reviewer->givePermissionTo('submissions.view-all');
        $this->actingAs($reviewer->fresh())->get(route('admin.submissions.show', $other))->assertOk();
    }

    #[Test]
    public function assigned_reviewer_records_a_revision_decision(): void
    {
        $reviewer = $this->reviewer();
        $submission = ManuscriptSubmission::factory()->stage(ManuscriptStage::InReview, $reviewer)->create();

        $this->actingAs($reviewer)->post(route('admin.submissions.decide', $submission), ['decision' => 'revision'])
            ->assertSessionHasErrors('reviewer_remarks');

        $this->actingAs($reviewer)->post(route('admin.submissions.decide', $submission), [
            'decision' => 'revision', 'reviewer_remarks' => 'Please expand Part III.',
        ])->assertSessionHas('success');

        $this->assertSame(ManuscriptStage::Revision, $submission->fresh()->stage);
        $this->assertDatabaseHas('manuscript_revisions', ['manuscript_submission_id' => $submission->id, 'decision' => 'revision', 'round' => 1]);
    }

    #[Test]
    public function other_reviewers_cannot_decide(): void
    {
        $assigned = $this->reviewer();
        $other = $this->reviewer();
        $submission = ManuscriptSubmission::factory()->stage(ManuscriptStage::InReview, $assigned)->create();

        $this->actingAs($other)->post(route('admin.submissions.decide', $submission), ['decision' => 'approved'])->assertForbidden();
    }

    #[Test]
    public function superadmin_assigns_and_reassigns_a_reviewer(): void
    {
        $category = ContentCategory::factory()->create();
        $first = $this->reviewer([], [$category->id]);
        $second = $this->reviewer([], [$category->id]);
        $submission = ManuscriptSubmission::factory()->stage(ManuscriptStage::PlagiarismAccepted)->create(['content_category_id' => $category->id]);
        $admin = $this->superadmin();

        $this->actingAs($admin)->post(route('admin.submissions.assign', $submission), ['reviewer_id' => $first->id])->assertSessionHas('success');
        $this->assertSame(ManuscriptStage::InReview, $submission->fresh()->stage);

        $this->actingAs($admin)->post(route('admin.submissions.assign', $submission), ['reviewer_id' => $second->id]);
        $this->assertSame($second->id, $submission->fresh()->assigned_to);
    }

    #[Test]
    public function pending_manuscripts_cannot_be_assigned(): void
    {
        $reviewer = $this->reviewer();
        $submission = ManuscriptSubmission::factory()->create();

        $this->actingAs($this->superadmin())->post(route('admin.submissions.assign', $submission), ['reviewer_id' => $reviewer->id])
            ->assertSessionHasErrors('reviewer_id');
    }

    #[Test]
    public function superadmin_can_override_stage(): void
    {
        $submission = ManuscriptSubmission::factory()->create();

        $this->actingAs($this->superadmin())->patch(route('admin.submissions.change-stage', $submission), ['stage' => 'rejected', 'remarks' => 'Out of scope'])
            ->assertSessionHas('success');

        $this->assertSame(ManuscriptStage::Rejected, $submission->fresh()->stage);
        $this->assertDatabaseHas('activity_logs', ['record_id' => (string) $submission->id, 'remarks' => 'Out of scope']);
    }

    #[Test]
    public function best_paper_winner_is_unique_per_period_and_requires_published(): void
    {
        $admin = $this->superadmin();
        $published = ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create();
        $another = ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create();
        $pending = ManuscriptSubmission::factory()->create();
        $award = ['period_type' => 'monthly', 'award_month' => 'August', 'award_year' => 2026, 'prize_amount' => 2000, 'editorial_citation' => 'Outstanding.'];

        $this->actingAs($admin)->post(route('admin.submissions.awards.store', $published), $award)->assertSessionHas('success');
        $this->actingAs($admin)->post(route('admin.submissions.awards.store', $another), $award)->assertSessionHasErrors('period_type');
        $this->actingAs($admin)->post(route('admin.submissions.awards.store', $pending), $award)->assertSessionHasErrors('period_type');

        // Quarterly awards are a separate award type.
        $this->actingAs($admin)->post(route('admin.submissions.awards.store', $another), ['period_type' => 'quarterly', 'award_quarter' => 'Q3'] + $award)
            ->assertSessionHas('success');

        $this->assertSame(2, BestPaperAward::count());
    }

    #[Test]
    public function admin_creates_a_submission_for_an_author_with_counted_words(): void
    {
        $content = ContentCategory::factory()->create(['min_word_limit' => 100, 'max_word_limit' => 5000]);
        $authorCategory = AuthorCategory::factory()->create();
        ManuscriptFee::create(['author_category_id' => $authorCategory->id, 'content_category_id' => $content->id, 'fees' => 1000]);
        $author = User::factory()->author($authorCategory)->create();

        $response = $this->actingAs($this->superadmin())->post(route('admin.submissions.store'), [
            'user_id' => $author->id,
            'author_category_id' => $authorCategory->id,
            'institution' => 'NLSIU',
            'country' => 'India',
            'co_authors' => ['Rahul Mistry', ''],
            'title' => 'Data Protection Revisited',
            'content_category_id' => $content->id,
            'keywords' => 'privacy, data, consent',
            'abstract' => 'Abstract text.',
            'manuscript' => Docx::withWords(750),
        ] + array_fill_keys(array_keys(ManuscriptSubmission::DECLARATIONS), '1'));

        $submission = ManuscriptSubmission::firstOrFail();
        $response->assertRedirect(route('admin.submissions.show', $submission));
        $this->assertSame(750, $submission->word_count);
        $this->assertSame(['Rahul Mistry'], $submission->co_authors);
        $this->assertSame(['privacy', 'data', 'consent'], $submission->keywords);
        Storage::disk('local')->assertExists($submission->manuscript_attachment);
    }

    #[Test]
    public function word_count_outside_category_limits_is_rejected(): void
    {
        $content = ContentCategory::factory()->create(['min_word_limit' => 1000, 'max_word_limit' => 2000]);
        $authorCategory = AuthorCategory::factory()->create();
        ManuscriptFee::create(['author_category_id' => $authorCategory->id, 'content_category_id' => $content->id, 'fees' => 1000]);

        $this->actingAs($this->superadmin())->post(route('admin.submissions.store'), [
            'user_id' => $this->author()->id,
            'author_category_id' => $authorCategory->id,
            'institution' => 'NLSIU', 'country' => 'India',
            'title' => 'Too Short', 'content_category_id' => $content->id,
            'keywords' => 'a, b, c', 'abstract' => 'x',
            'manuscript' => Docx::withWords(200),
        ] + array_fill_keys(array_keys(ManuscriptSubmission::DECLARATIONS), '1'))
            ->assertSessionHasErrors('manuscript');
    }

    #[Test]
    public function manuscripts_with_paid_fees_cannot_be_deleted(): void
    {
        $admin = $this->superadmin();
        $paid = ManuscriptSubmission::factory()->create();
        Payment::factory()->paid()->create(['payable_id' => $paid->id]);
        $unpaid = ManuscriptSubmission::factory()->create();

        $this->actingAs($admin)->delete(route('admin.submissions.destroy', $paid))->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.submissions.destroy', $unpaid))->assertRedirect();
        $this->assertModelMissing($unpaid);
    }

    #[Test]
    public function payments_and_plagiarism_screens_render_with_invoice(): void
    {
        $admin = $this->superadmin();
        $submission = ManuscriptSubmission::factory()->create();
        $payment = Payment::factory()->paid()->create(['payable_id' => $submission->id, 'billing_details' => ['name' => 'Ananya', 'address_line1' => '1 Court Rd', 'city' => 'Mumbai']]);
        $check = PlagiarismCheck::factory()->create(['similarity_percentage' => 4.2, 'check_status' => 'completed', 'payment_id' => $payment->id]);

        $this->actingAs($admin)->get(route('admin.payments.index'))->assertOk()->assertSee($submission->reference());
        $this->actingAs($admin)->get(route('admin.payments.show', $payment))->assertOk()->assertSee('Billing snapshot');
        $this->actingAs($admin)->get(route('admin.payments.invoice', $payment))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($admin)->get(route('admin.plagiarism-checks.index'))->assertOk()->assertSee('4.20%');
        $this->actingAs($admin)->get(route('admin.plagiarism-checks.show', $check))->assertOk();
    }

    #[Test]
    public function admin_can_recheck_a_plagiarism_check_keeping_history(): void
    {
        config(['services.plagiarism.fake_similarity' => '2.5']);
        $check = PlagiarismCheck::factory()->create(['check_status' => 'completed', 'similarity_percentage' => 3]);

        $this->actingAs($this->superadmin())->post(route('admin.plagiarism-checks.recheck', $check))->assertRedirect();

        $this->assertSame(2, PlagiarismCheck::count());
        $this->assertEquals(2.5, (float) PlagiarismCheck::latest('id')->first()->similarity_percentage);
        $this->assertEquals(3.0, (float) $check->fresh()->similarity_percentage);
    }

    #[Test]
    public function reviewer_without_payment_permission_cannot_see_payments(): void
    {
        $this->actingAs($this->reviewer())->get(route('admin.payments.index'))->assertForbidden();
    }
}
