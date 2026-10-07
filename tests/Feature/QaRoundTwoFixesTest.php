<?php

namespace Tests\Feature;

use App\Enums\PlagiarismCheckStatus;
use App\Enums\PlagiarismCheckType;
use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use App\Models\ContentCategoryTheme;
use App\Models\PlagiarismCheck;
use App\Models\User;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Fixes from the second browser QA pass: one theme per category per month, no free re-runs of unpaid
 * standalone checks, unpaid checks on the author dashboard, and auth status messages shown once.
 */
class QaRoundTwoFixesTest extends TestCase
{
    #[Test]
    public function a_content_category_has_only_one_theme_per_month(): void
    {
        $category = ContentCategory::factory()->create();
        $admin = $this->superadmin();
        $data = ['content_category_id' => $category->id, 'name' => 'AI and the Courts', 'volume' => 5, 'period' => '2027-03'];

        $this->actingAs($admin)->post(route('admin.themes.store'), $data)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.themes.store'), ['name' => 'Second theme'] + $data)->assertSessionHasErrors('period');
        $this->assertSame(1, ContentCategoryTheme::count());

        // Editing the existing theme in the same month is fine; another month or category is fine too.
        $theme = ContentCategoryTheme::firstOrFail();
        $this->actingAs($admin)->put(route('admin.themes.update', $theme), ['name' => 'Renamed'] + $data)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.themes.store'), ['period' => '2027-04'] + $data)->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.themes.store'), ['content_category_id' => ContentCategory::factory()->create()->id] + $data)->assertSessionHasNoErrors();
    }

    #[Test]
    public function an_unpaid_standalone_check_cannot_be_re_run(): void
    {
        Bus::fake();
        $check = PlagiarismCheck::factory()->create(['check_type' => PlagiarismCheckType::Standalone, 'payment_id' => null, 'check_status' => PlagiarismCheckStatus::Pending]);
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('admin.plagiarism-checks.show', $check))->assertOk()->assertDontSee('Re-check');
        $this->actingAs($admin)->post(route('admin.plagiarism-checks.recheck', $check))->assertSessionHas('error');
        $this->assertSame(1, PlagiarismCheck::count());
        Bus::assertNothingDispatched();
    }

    #[Test]
    public function the_author_dashboard_lists_unpaid_plagiarism_checks(): void
    {
        $author = User::factory()->author(AuthorCategory::factory()->create())->create();
        PlagiarismCheck::factory()->create(['user_id' => $author->id, 'title' => 'My unpaid check', 'payment_id' => null]);

        $this->actingAs($author)->get(route('account.dashboard'))->assertOk()
            ->assertSee('My unpaid check')->assertSee('Pay to run check')->assertDontSee('Nothing needs your attention right now.');
    }

    #[Test]
    public function the_registration_code_message_is_shown_once(): void
    {
        $response = $this->withSession([
            'pending_registration' => ['email' => 'new@example.com'],
            'status' => "We've emailed a 6-digit code to new@example.com.",
        ])->get(route('register.verify'));

        $response->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), 'emailed a 6-digit code'));
    }
}
