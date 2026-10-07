<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStage;
use App\Enums\PaymentPurpose;
use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ManuscriptCoAuthorFee;
use App\Models\ManuscriptFee;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Models\PublicationCertificate;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

/**
 * Fixes from the browser QA pass: double-blind review, empty archive ZIP, branded error pages,
 * the publication-fee breakdown on the pay page and co-authors on certificate verification.
 */
class QaFixesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    #[Test]
    public function reviewers_do_not_see_who_wrote_the_manuscript_but_superadmins_do(): void
    {
        $content = ContentCategory::factory()->create();
        $reviewer = $this->reviewer([], [$content->id]);
        $submission = ManuscriptSubmission::factory()->stage(ManuscriptStage::InReview, $reviewer)->create([
            'content_category_id' => $content->id, 'co_authors' => ['Rahul Mehta'], 'institution' => 'Secret Law School',
        ]);
        $author = $submission->author;

        $this->actingAs($reviewer)->get(route('admin.submissions.show', $submission))->assertOk()
            ->assertSee('hidden for double-blind peer review')
            ->assertDontSee($author->email)->assertDontSee('Secret Law School')->assertDontSee('Rahul Mehta');
        $this->actingAs($reviewer)->get(route('admin.submissions.index'))->assertOk()->assertDontSee($author->name);
        // Searching by the author's name finds nothing for a reviewer.
        $this->actingAs($reviewer)->get(route('admin.submissions.index', ['search' => $author->name]))->assertDontSee($submission->title);

        $this->actingAs($this->superadmin())->get(route('admin.submissions.show', $submission))->assertOk()
            ->assertSee($author->email)->assertSee('Secret Law School')->assertSee('Rahul Mehta');
        $this->actingAs($this->superadmin())->get(route('admin.submissions.index', ['search' => $author->name]))->assertSee($submission->title);
    }

    #[Test]
    public function the_archive_zip_explains_when_no_files_are_available_instead_of_failing(): void
    {
        $missing = ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create(['published_at' => now(), 'manuscript_attachment' => 'manuscripts/gone.docx']);

        $this->get(route('archive.zip', ['category' => $missing->content_category_id]))
            ->assertNotFound()->assertSee('The manuscript files for these filters are not available for download.');

        $missing->update(['manuscript_attachment' => Docx::withWords(50)->store('manuscripts', 'local')]);
        $this->get(route('archive.zip', ['category' => $missing->content_category_id]))->assertOk()->assertHeader('content-type', 'application/zip');
    }

    #[Test]
    public function error_pages_are_branded_and_hide_framework_messages(): void
    {
        $this->get('/this-page-does-not-exist')->assertNotFound()
            ->assertSee('Page not found')->assertSee('Go to home page');

        // A missing record must not reveal model class names.
        $this->get(route('archive.show', 999999))->assertNotFound()->assertSee('Page not found')->assertDontSee('No query results')->assertDontSee('App\\Models', false);
    }

    #[Test]
    public function the_pay_page_shows_the_co_author_surcharge_breakdown(): void
    {
        $content = ContentCategory::factory()->create();
        $authorCategory = AuthorCategory::factory()->create();
        ManuscriptFee::create(['author_category_id' => $authorCategory->id, 'content_category_id' => $content->id, 'fees' => 1800]);
        ManuscriptCoAuthorFee::create(['content_category_id' => $content->id, 'first_two_fee' => 270, 'additional_fee' => 180]);
        $author = \App\Models\User::factory()->author($authorCategory)->create();
        $submission = ManuscriptSubmission::factory()->stage(ManuscriptStage::Approved)->create([
            'user_id' => $author->id, 'content_category_id' => $content->id, 'author_category_id' => $authorCategory->id, 'co_authors' => ['A'],
        ]);
        $payment = Payment::factory()->create([
            'user_id' => $submission->user_id, 'payable_id' => $submission->id, 'payment_purpose' => PaymentPurpose::Publication,
            'amount' => 2070, 'tax_amount' => 0, 'gateway_order_id' => null,
        ]);

        $this->actingAs($submission->author)->get(route('account.payments.pay', $payment))->assertOk()
            ->assertSee('Co-author surcharge')->assertSee(money(270), false)->assertSee(money(1800), false);
    }

    #[Test]
    public function certificate_verification_lists_co_authors(): void
    {
        $submission = ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create(['published_at' => now()]);
        $certificate = PublicationCertificate::create([
            'manuscript_submission_id' => $submission->id, 'certificate_number' => 'MLR/COP/2026/000099', 'verification_slug' => 'qa-verify-slug',
            'issued_at' => now(), 'document_path' => 'certificates/x.pdf',
            'snapshot_json' => ['reference' => 'MOS-00099', 'title' => 'T', 'author' => 'Ananya', 'co_authors' => ['Rahul Mehta', 'Kavita Rao'], 'content_category' => 'Notes', 'published_at' => now()->toDateString()],
        ]);

        $this->get(route('certificates.verify', $certificate->verification_slug))->assertOk()
            ->assertSee('Co-authors')->assertSee('Rahul Mehta, Kavita Rao');
    }
}
