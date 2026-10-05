<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\UploadLimits;
use Illuminate\Http\UploadedFile;
use Database\Seeders\PageSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UploadLimitsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PageSeeder::class);
    }

    #[Test]
    public function the_effective_limit_never_exceeds_php_settings(): void
    {
        $this->assertLessThanOrEqual(UploadLimits::serverBytes(), UploadLimits::bytes(20480));
        $this->assertLessThanOrEqual(20480 * 1024, UploadLimits::bytes(20480));
        $this->assertSame(1024 * 1024, UploadLimits::bytes(1024) <= UploadLimits::serverBytes() ? UploadLimits::bytes(1024) : 1024 * 1024);
        $this->assertStringEndsWith(' MB', UploadLimits::label(20480));
    }

    #[Test]
    public function a_file_php_rejected_for_size_gets_a_clear_message(): void
    {
        $tooBig = new UploadedFile(__FILE__, 'manuscript.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', UPLOAD_ERR_INI_SIZE, true);

        $this->actingAs(User::factory()->author()->create())
            ->post(route('account.submissions.store'), ['title' => 'X', 'manuscript' => $tooBig])
            ->assertSessionHasErrors(['manuscript' => 'This file is too large to upload. The maximum is '.UploadLimits::label(PHP_INT_MAX >> 10).'.']);
    }

    #[Test]
    public function manuscript_inputs_carry_the_real_limit(): void
    {
        $this->actingAs(User::factory()->author()->create())->get(page_url('submit'))
            ->assertSee('data-rule-maxbytes="'.UploadLimits::bytes(20480).'"', false);
    }
}
