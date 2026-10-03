<?php

namespace Tests\Feature\Workflow;

use App\Enums\ManuscriptStage;
use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Enums\PlagiarismCheckStatus;
use App\Enums\RevisionDecision;
use App\Models\Address;
use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ManuscriptFee;
use App\Models\ManuscriptSubmission;
use App\Models\User;
use App\Services\Manuscripts\DocxWordCounter;
use App\Services\Manuscripts\ManuscriptWorkflow;
use App\Services\Manuscripts\ReviewerAllocator;
use App\Services\Payments\PaymentService;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

class ManuscriptWorkflowTest extends TestCase
{
    private ContentCategory $content;

    private AuthorCategory $authorCategory;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Mail::fake();
        $this->seed(NotificationTemplateSeeder::class);
        $this->superadmin();

        $this->content = ContentCategory::factory()->create();
        $this->authorCategory = AuthorCategory::factory()->create();
        ManuscriptFee::create(['author_category_id' => $this->authorCategory->id, 'content_category_id' => $this->content->id, 'fees' => 2500]);
    }

    private function submission(): ManuscriptSubmission
    {
        $author = User::factory()->author($this->authorCategory)->create();
        $path = Docx::withWords(300)->store('manuscripts', 'local');

        return ManuscriptSubmission::factory()->create([
            'user_id' => $author->id,
            'author_category_id' => $this->authorCategory->id,
            'content_category_id' => $this->content->id,
            'manuscript_attachment' => $path,
        ]);
    }

    private function address(User $user, string $country = 'IN'): Address
    {
        return $user->address()->create([
            'recipient_name' => $user->name, 'address_line1' => '1 Court Road', 'city' => 'Mumbai', 'state' => $country === 'IN' ? 'Maharashtra' : null, 'country_code' => $country,
        ]);
    }

    #[Test]
    public function docx_word_counter_counts_words(): void
    {
        $file = Docx::withWords(1234);

        $this->assertSame(1234, app(DocxWordCounter::class)->count($file->getRealPath()));
    }

    #[Test]
    public function full_pipeline_from_submission_to_published_certificate(): void
    {
        config(['services.plagiarism.fake_similarity' => '7.5']);
        $reviewer = $this->reviewer([], [$this->content->id]);
        $submission = $this->submission();
        $workflow = app(ManuscriptWorkflow::class);
        $payments = app(PaymentService::class);

        $workflow->submitted($submission);
        $this->assertSame(ManuscriptStage::Pending, $submission->fresh()->stage);

        // Pre-screening fee (India → 18% tax) triggers the plagiarism check (sync queue in tests).
        $payment = $payments->createPending($submission->author, $submission, PaymentPurpose::Prescreening, 150, $this->address($submission->author));
        // Tax-inclusive: the payer is charged exactly ₹150, of which ₹22.88 is GST.
        $this->assertEquals(22.88, (float) $payment->tax_amount);
        $this->assertEquals(150.0, (float) $payment->fresh()->total_amount);
        $payments->markPaid($payment, 'pay_test_1', 'upi', 'UPI test@upi');

        $submission->refresh();
        $this->assertSame(ManuscriptStage::InReview, $submission->stage);
        $this->assertSame($reviewer->id, $submission->assigned_to);
        $this->assertEquals(7.5, (float) $submission->plagiarism_similarity);
        $check = $submission->plagiarismChecks()->firstOrFail();
        $this->assertSame(PlagiarismCheckStatus::Completed, $check->check_status);
        Storage::disk('local')->assertExists($check->report_file);
        $this->assertNotNull($payment->fresh()->invoice_number);

        // Reviewer asks for a revision, author resubmits, reviewer approves.
        $workflow->decide($submission, $reviewer, RevisionDecision::Revision, 'Tighten section 2.');
        $this->assertSame(ManuscriptStage::Revision, $submission->fresh()->stage);

        $workflow->resubmit($submission->fresh(), 'manuscripts/v2.docx', 4100, 'Done.');
        $this->assertSame(ManuscriptStage::Resubmitted, $submission->fresh()->stage);
        $this->assertSame('manuscripts/v2.docx', $submission->revisions()->first()->resubmitted_attachment);

        $workflow->decide($submission->fresh(), $reviewer, RevisionDecision::Approved, null);
        $this->assertSame(ManuscriptStage::Approved, $submission->fresh()->stage);

        // Publication fee from the matrix → published + certificate.
        $publication = $payments->createPending($submission->author, $submission, PaymentPurpose::Publication, 2500, $submission->author->address);
        $payments->markPaid($publication, 'pay_test_2');

        $submission->refresh();
        $this->assertSame(ManuscriptStage::Published, $submission->stage);
        $this->assertNotNull($submission->published_at);
        $this->assertNotNull($submission->certificate);
        Storage::disk('local')->assertExists($submission->certificate->document_path);
        $this->assertDatabaseHas('notification_logs', ['template_slug' => 'submission_published', 'channel' => 'email']);
    }

    #[Test]
    public function similarity_above_threshold_rejects_and_exactly_ten_percent_is_accepted(): void
    {
        $workflow = app(ManuscriptWorkflow::class);

        $rejected = $this->submission();
        $workflow->applyPlagiarismResult($rejected, 10.01);
        $this->assertSame(ManuscriptStage::Rejected, $rejected->fresh()->stage);

        $accepted = $this->submission();
        $workflow->applyPlagiarismResult($accepted, 10.00);
        // No reviewer covers the category → stays plagiarism_accepted, pending assignment.
        $this->assertSame(ManuscriptStage::PlagiarismAccepted, $accepted->fresh()->stage);
        $this->assertNull($accepted->fresh()->assigned_to);
        $this->assertDatabaseHas('notification_logs', ['template_slug' => 'submission_unassigned']);
    }

    #[Test]
    public function allocator_prefers_lowest_workload_then_longest_wait(): void
    {
        $busy = $this->reviewer([], [$this->content->id]);
        $waitedLong = $this->reviewer([], [$this->content->id]);
        $recent = $this->reviewer([], [$this->content->id]);
        $this->reviewer([], [ContentCategory::factory()->create()->id]); // other category

        ManuscriptSubmission::factory()->stage(ManuscriptStage::InReview, $busy)->create(['content_category_id' => $this->content->id]);
        ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create(['assigned_to' => $waitedLong->id, 'assigned_at' => now()->subDays(30)]);
        ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create(['assigned_to' => $recent->id, 'assigned_at' => now()->subDay()]);

        $allocator = app(ReviewerAllocator::class);
        $this->assertSame($waitedLong->id, $allocator->pick($this->content->id)->id);
        $this->assertCount(3, $allocator->eligible($this->content->id));

        $recent->update(['status' => 'Inactive']);
        $this->assertCount(2, $allocator->eligible($this->content->id));
    }

    #[Test]
    public function marking_a_payment_paid_twice_fulfils_once(): void
    {
        config(['services.plagiarism.fake_similarity' => '3']);
        $submission = $this->submission();
        $payments = app(PaymentService::class);
        $payment = $payments->createPending($submission->author, $submission, PaymentPurpose::Prescreening, 150, $this->address($submission->author, 'US'));

        $this->assertEquals(0.0, (float) $payment->tax_amount);
        $this->assertEquals(150.0, (float) $payment->fresh()->total_amount); // same price, zero-rated

        $payments->markPaid($payment, 'pay_a');
        $payments->markPaid($payment->fresh(), 'pay_b');

        $this->assertSame(1, $submission->plagiarismChecks()->count());
        $this->assertSame('pay_a', $payment->fresh()->payment_id);
        $this->assertSame(PaymentStatus::Paid, $payment->fresh()->payment_status);
    }

    #[Test]
    public function zero_publication_fee_publishes_on_approval(): void
    {
        ManuscriptFee::query()->update(['fees' => 0]);
        $reviewer = $this->reviewer([], [$this->content->id]);
        $submission = $this->submission();
        $submission->forceFill(['stage' => ManuscriptStage::InReview, 'assigned_to' => $reviewer->id])->save();

        app(ManuscriptWorkflow::class)->decide($submission, $reviewer, RevisionDecision::Approved, null);

        $this->assertSame(ManuscriptStage::Published, $submission->fresh()->stage);
    }
}
