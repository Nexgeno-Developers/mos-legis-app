<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStage;
use App\Enums\PaymentPurpose;
use App\Enums\RevisionDecision;
use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ManuscriptCoAuthorFee;
use App\Models\ManuscriptFee;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Models\User;
use App\Services\Manuscripts\FeeCalculator;
use App\Services\Manuscripts\ManuscriptWorkflow;
use Database\Seeders\CatalogueSeeder;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

/**
 * Co-author surcharge: each of the 1st/2nd co-authors adds the first rate, each further co-author the
 * second rate, on top of the publication fee from the Author × Content matrix.
 */
class CoAuthorSurchargeTest extends TestCase
{
    private ContentCategory $content;

    private AuthorCategory $authorCategory;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();

        $this->content = ContentCategory::factory()->create(['name' => 'Research Articles', 'min_word_limit' => 100, 'max_word_limit' => 5000]);
        $this->authorCategory = AuthorCategory::factory()->create();
        ManuscriptFee::create(['author_category_id' => $this->authorCategory->id, 'content_category_id' => $this->content->id, 'fees' => 2000]);
        ManuscriptCoAuthorFee::create(['content_category_id' => $this->content->id, 'first_two_fee' => 300, 'additional_fee' => 200]);
    }

    private function submission(array $coAuthors, ManuscriptStage $stage = ManuscriptStage::Pending): ManuscriptSubmission
    {
        $author = User::factory()->author($this->authorCategory)->create();

        return ManuscriptSubmission::factory()->stage($stage)->create([
            'user_id' => $author->id,
            'author_category_id' => $this->authorCategory->id,
            'content_category_id' => $this->content->id,
            'co_authors' => $coAuthors,
            'manuscript_attachment' => Docx::withWords(300)->store('manuscripts', 'local'),
        ]);
    }

    #[Test]
    public function first_two_co_authors_pay_the_first_rate_and_the_rest_the_second(): void
    {
        $fees = app(FeeCalculator::class);

        foreach ([0 => 0.0, 1 => 300.0, 2 => 600.0, 3 => 800.0, 5 => 1200.0] as $count => $expected) {
            $this->assertSame($expected, $fees->coAuthorSurcharge($this->content->id, $count), "{$count} co-authors");
        }

        // A category without surcharge rates adds nothing.
        $this->assertSame(0.0, $fees->coAuthorSurcharge(ContentCategory::factory()->create()->id, 4));
    }

    #[Test]
    public function the_publication_fee_includes_the_surcharge_for_named_co_authors(): void
    {
        $fees = app(FeeCalculator::class);

        $this->assertSame(2000.0, $fees->publicationFeeFor($this->submission([])));
        $this->assertSame(2300.0, $fees->publicationFeeFor($this->submission(['Asha Rao', ''])));

        $breakdown = $fees->publicationBreakdown($this->submission(['A', 'B', 'C']));
        $this->assertSame(['base' => 2000.0, 'co_authors' => 3, 'first_two_fee' => 300.0, 'additional_fee' => 200.0, 'surcharge' => 800.0, 'total' => 2800.0], $breakdown);

        // A combination that isn't offered stays "not offered", whatever the co-authors.
        ManuscriptFee::query()->delete();
        $this->assertNull($fees->publicationFeeFor($this->submission(['A'])));
    }

    #[Test]
    public function the_catalogue_grid_matches_the_published_rates(): void
    {
        $this->assertSame([300, 200], CatalogueSeeder::coAuthorRatesFor('Research Articles'));
        $this->assertSame([270, 180], CatalogueSeeder::coAuthorRatesFor('Legislative Commentaries'));
        $this->assertSame([270, 180], CatalogueSeeder::coAuthorRatesFor('Policy Papers / Policy Briefs'));
        $this->assertSame([225, 150], CatalogueSeeder::coAuthorRatesFor('Case Commentaries (Case Notes)'));
        $this->assertSame([180, 120], CatalogueSeeder::coAuthorRatesFor('Short Articles / Essays'));
        $this->assertSame([180, 120], CatalogueSeeder::coAuthorRatesFor('Notes'));
        $this->assertSame([150, 100], CatalogueSeeder::coAuthorRatesFor('Book Reviews'));
        $this->assertSame([420, 280], CatalogueSeeder::coAuthorRatesFor('Book Chapters (Call for Chapters)'));
        $this->assertNull(CatalogueSeeder::coAuthorRatesFor('Something Else'));
    }

    #[Test]
    public function admin_manages_the_surcharge_grid_on_the_fees_page(): void
    {
        $admin = $this->superadmin();
        $other = ContentCategory::factory()->create(['name' => 'Book Reviews']);

        $this->actingAs($admin)->get(route('admin.fees.index'))->assertOk()
            ->assertSee('Co-Author Surcharge')
            ->assertSee('name="coauthor_fees['.$this->content->id.'][first_two]"', false)
            ->assertSee('value="300"', false);

        $this->actingAs($admin)->put(route('admin.fees.update'), [
            'fees' => [$this->authorCategory->id => [$this->content->id => '2500', $other->id => '']],
            'coauthor_fees' => [
                $this->content->id => ['first_two' => '', 'additional' => ''],   // both blank → removed
                $other->id => ['first_two' => '150', 'additional' => '100'],
            ],
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseMissing('manuscript_coauthor_fees', ['content_category_id' => $this->content->id]);
        $this->assertSame([150.0, 100.0], app(FeeCalculator::class)->coAuthorRates($other->id));
        $this->assertEquals(2500, ManuscriptFee::where('content_category_id', $this->content->id)->value('fees'));

        // Invalid values and unknown categories are refused.
        $this->actingAs($admin)->put(route('admin.fees.update'), ['coauthor_fees' => [$other->id => ['first_two' => '-5']]])
            ->assertSessionHasErrors("coauthor_fees.{$other->id}.first_two");
        $this->actingAs($admin)->put(route('admin.fees.update'), ['coauthor_fees' => [999999 => ['first_two' => '10']]])
            ->assertSessionHasErrors('coauthor_fees');

        // Saving only the matrix leaves the surcharge grid alone.
        $this->actingAs($admin)->put(route('admin.fees.update'), ['fees' => [$this->authorCategory->id => [$this->content->id => '2600']]])
            ->assertSessionHasNoErrors();
        $this->assertSame([150.0, 100.0], app(FeeCalculator::class)->coAuthorRates($other->id));
    }

    #[Test]
    public function the_submit_page_lists_the_surcharge_and_gives_the_form_the_rates(): void
    {
        $this->seed(PageSeeder::class);
        $author = User::factory()->author($this->authorCategory)->create();

        $this->actingAs($author)->get(page_url('submit'))->assertOk()
            ->assertSee('Co-author surcharge')
            ->assertSee('3rd co-author onwards (each)')
            ->assertSee('coAuthorFees')
            ->assertSee('co-authors-changed');
    }

    #[Test]
    public function checkout_and_approval_charge_the_fee_with_the_surcharge(): void
    {
        $this->seed(NotificationTemplateSeeder::class);
        $submission = $this->submission(['A', 'B', 'C'], ManuscriptStage::PlagiarismAccepted);
        $reviewer = $this->reviewer([], [$this->content->id]);
        $workflow = app(ManuscriptWorkflow::class);
        $workflow->assign($submission, $reviewer, $this->superadmin());
        $workflow->decide($submission->fresh(), $reviewer, RevisionDecision::Approved, null);
        $this->assertSame(ManuscriptStage::Approved, $submission->fresh()->stage);

        $author = $submission->author;
        $this->actingAs($author)->get(route('account.submissions.show', $submission))->assertOk()
            ->assertSee('Publication fee: '.money(2800), false)
            ->assertSee('co-author surcharge '.money(800).' (3 co-authors)', false);

        $this->actingAs($author)->get(route('account.checkout.submission', [$submission, 'publication']))->assertOk()
            ->assertSee('Co-author surcharge')->assertSee(money(800), false)->assertSee(money(2800), false);

        $this->actingAs($author)->post(route('account.checkout.store'), [
            'payable_type' => 'manuscript_submissions', 'payable_id' => $submission->id, 'purpose' => PaymentPurpose::Publication->value,
            'recipient_name' => 'Asha Rao', 'address_line1' => '1 Court Road', 'city' => 'Austin', 'country_code' => 'US', 'state' => 'Texas',
        ])->assertRedirect();
        $this->assertEquals(2800.0, (float) Payment::latest('id')->firstOrFail()->total_amount);

        $this->actingAs($this->superadmin())->get(route('admin.submissions.show', $submission))->assertOk()
            ->assertSee('co-author surcharge '.money(800), false);
    }

    #[Test]
    public function a_free_category_with_co_authors_still_asks_for_the_surcharge(): void
    {
        $this->seed(NotificationTemplateSeeder::class);
        ManuscriptFee::query()->update(['fees' => 0]);
        $reviewer = $this->reviewer([], [$this->content->id]);
        $workflow = app(ManuscriptWorkflow::class);

        $solo = $this->submission([], ManuscriptStage::PlagiarismAccepted);
        $workflow->assign($solo, $reviewer, $this->superadmin());
        $workflow->decide($solo->fresh(), $reviewer, RevisionDecision::Approved, null);
        $this->assertSame(ManuscriptStage::Published, $solo->fresh()->stage);

        $team = $this->submission(['A'], ManuscriptStage::PlagiarismAccepted);
        $workflow->assign($team, $reviewer, $this->superadmin());
        $workflow->decide($team->fresh(), $reviewer, RevisionDecision::Approved, null);
        $this->assertSame(ManuscriptStage::Approved, $team->fresh()->stage);
        $this->assertSame(300.0, app(FeeCalculator::class)->publicationFeeFor($team));
    }
}
