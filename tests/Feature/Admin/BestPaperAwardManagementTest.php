<?php

namespace Tests\Feature\Admin;

use App\Enums\ManuscriptStage;
use App\Models\BestPaperAward;
use App\Models\ManuscriptSubmission;
use Database\Seeders\NotificationTemplateSeeder;
use Database\Seeders\PageSeeder;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Admin → Manuscripts → Best Paper Awards.
 */
class BestPaperAwardManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seed(NotificationTemplateSeeder::class);
    }

    private function payload(ManuscriptSubmission $submission, array $overrides = []): array
    {
        return $overrides + [
            'manuscript_submission_id' => $submission->id, 'award_quarter' => 'Q3',
            'award_year' => 2026, 'prize_amount' => '', 'editorial_citation' => 'Careful doctrinal analysis.',
        ];
    }

    #[Test]
    public function the_module_lists_awards_and_offers_published_manuscripts_only(): void
    {
        $published = ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create(['title' => 'Federalism Revisited']);
        $pending = ManuscriptSubmission::factory()->create(['title' => 'Still Pending']);
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertSee(route('admin.best-paper-awards.index'), false); // in the menu
        $this->actingAs($admin)->get(route('admin.best-paper-awards.index'))->assertOk()->assertSee('No winner chosen yet.');
        $this->actingAs($admin)->get(route('admin.best-paper-awards.create', ['submission' => $published->id]))->assertOk()
            ->assertSee('Federalism Revisited')->assertDontSee('Still Pending');

        $this->actingAs($admin)->post(route('admin.best-paper-awards.store'), $this->payload($pending))->assertSessionHasErrors('manuscript_submission_id');
        $this->actingAs($admin)->post(route('admin.best-paper-awards.store'), $this->payload($published))
            ->assertRedirect(route('admin.best-paper-awards.index'))->assertSessionHas('success');

        $award = BestPaperAward::firstOrFail();
        $this->assertNull($award->prize_amount); // the prize is optional
        $this->assertDatabaseHas('notification_logs', ['template_slug' => 'best_paper_selected']);
        $this->actingAs($admin)->get(route('admin.best-paper-awards.index'))->assertSee('Federalism Revisited')->assertSee('Q3 2026');
    }

    #[Test]
    public function one_winner_per_quarter_and_awards_can_be_edited_and_removed(): void
    {
        [$first, $second] = ManuscriptSubmission::factory()->count(2)->stage(ManuscriptStage::Published)->create();
        $admin = $this->superadmin();

        $this->actingAs($admin)->post(route('admin.best-paper-awards.store'), $this->payload($first));
        $this->actingAs($admin)->post(route('admin.best-paper-awards.store'), $this->payload($second))->assertSessionHasErrors('award_quarter');
        // Another quarter is a separate period.
        $this->actingAs($admin)->post(route('admin.best-paper-awards.store'), $this->payload($second, ['award_quarter' => 'Q2']))->assertSessionHasNoErrors();

        $award = BestPaperAward::where('award_quarter', 'Q3')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.best-paper-awards.edit', $award))->assertOk();
        $this->actingAs($admin)->put(route('admin.best-paper-awards.update', $award), $this->payload($second, ['award_quarter' => 'Q4', 'prize_amount' => '5000']))->assertSessionHasNoErrors();
        $this->assertSame($second->id, $award->fresh()->manuscript_submission_id);
        $this->assertTrue($award->fresh()->hasPrize());

        $this->actingAs($admin)->delete(route('admin.best-paper-awards.destroy', $award))->assertSessionHas('success');
        $this->assertModelMissing($award);
        $this->assertSame(1, BestPaperAward::count());
    }

    #[Test]
    public function the_website_shows_the_winner_and_hides_an_empty_prize(): void
    {
        $this->seed(PageSeeder::class);
        $published = ManuscriptSubmission::factory()->stage(ManuscriptStage::Published)->create();
        $this->actingAs($this->superadmin())->post(route('admin.best-paper-awards.store'), $this->payload($published));

        $this->get(page_url('paper_winner'))->assertOk()->assertSee($published->title)->assertDontSee('Cash prize');
    }

    #[Test]
    public function only_admins_with_the_best_paper_permission_can_manage_awards(): void
    {
        $reviewer = $this->reviewer();
        $this->actingAs($reviewer)->get(route('admin.best-paper-awards.index'))->assertForbidden();

        $granted = $this->reviewer(['submissions.best-paper']);
        $this->actingAs($granted)->get(route('admin.best-paper-awards.index'))->assertOk();
    }
}
