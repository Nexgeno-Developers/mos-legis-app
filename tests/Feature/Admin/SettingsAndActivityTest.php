<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Support\Settings;
use App\Support\SettingsRegistry;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SettingsAndActivityTest extends TestCase
{
    private function validSettings(array $overrides = []): array
    {
        $data = [];
        foreach (SettingsRegistry::GROUPS as $group => $definition) {
            foreach ($definition['fields'] as $key => $field) {
                if ($field['type'] !== 'image') {
                    $data["{$group}_{$key}"] = $field['default'];
                }
            }
        }

        return array_merge($data, $overrides);
    }

    #[Test]
    public function settings_page_renders_all_groups(): void
    {
        $this->actingAs($this->superadmin())->get(route('admin.settings.edit'))
            ->assertOk()
            ->assertSee('Plagiarism Pre-Screening Fee')
            ->assertSee('Tax Rate Percentage')
            ->assertSee('Author Blog Posts Require Approval');
    }

    #[Test]
    public function settings_are_saved_and_cache_is_refreshed(): void
    {
        Storage::fake('public');
        $this->assertSame('150', settings('manuscript.plagiarism_prescreening_fee'));

        $this->actingAs($this->superadmin())->put(route('admin.settings.update'), $this->validSettings([
            'manuscript_plagiarism_prescreening_fee' => '200',
            'general_blog_author_approval_required' => '0',
            'general_application_logo' => UploadedFile::fake()->create('logo.png', 12, 'image/png'),
        ]))->assertSessionHasNoErrors();

        $settings = app(Settings::class);
        $this->assertSame('200', $settings->get('manuscript.plagiarism_prescreening_fee'));
        $this->assertFalse($settings->bool('general.blog_author_approval_required'));
        Storage::disk('public')->assertExists($settings->get('general.application_logo'));
    }

    #[Test]
    public function similarity_threshold_must_be_a_percentage(): void
    {
        $this->actingAs($this->superadmin())->put(route('admin.settings.update'), $this->validSettings([
            'manuscript_plagiarism_max_similarity_percent' => '150',
        ]))->assertSessionHasErrors('manuscript_plagiarism_max_similarity_percent');
    }

    #[Test]
    public function reviewer_cannot_change_settings(): void
    {
        $this->actingAs($this->reviewer())->put(route('admin.settings.update'), $this->validSettings())->assertForbidden();
    }

    #[Test]
    public function activity_logs_can_be_filtered_and_old_entries_purged(): void
    {
        $admin = $this->superadmin();
        ActivityLog::create(['module' => 'Blogs', 'action' => 'Created blog', 'remarks' => 'fresh']);
        $old = ActivityLog::create(['module' => 'Users', 'action' => 'Deleted user']);
        $old->forceFill(['created_at' => now()->subDays(45)])->save();

        $this->actingAs($admin)->get(route('admin.activity-logs.index', ['module' => 'Blogs']))
            ->assertOk()->assertSee('Created blog')->assertDontSee('Deleted user');

        $this->actingAs($admin)->delete(route('admin.activity-logs.purge'))->assertSessionHas('success');

        $this->assertModelMissing($old);
        $this->assertDatabaseHas('activity_logs', ['action' => 'Created blog']);
    }

    #[Test]
    public function scheduled_prune_removes_logs_older_than_30_days(): void
    {
        $old = ActivityLog::create(['module' => 'Users', 'action' => 'Old']);
        $old->forceFill(['created_at' => now()->subDays(31)])->save();
        $recent = ActivityLog::create(['module' => 'Users', 'action' => 'Recent']);

        $this->artisan('model:prune', ['--model' => [ActivityLog::class]])->assertSuccessful();

        $this->assertModelMissing($old);
        $this->assertModelExists($recent);
    }

    #[Test]
    public function activity_log_redacts_passwords(): void
    {
        $log = activity()->log('Users', 'Created user', null, ['email' => 'a@b.c', 'password' => 'secret']);

        $this->assertArrayNotHasKey('password', $log->payload);
    }
}
