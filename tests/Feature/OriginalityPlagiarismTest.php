<?php

namespace Tests\Feature;

use App\Enums\ManuscriptStage;
use App\Enums\PlagiarismCheckStatus;
use App\Enums\PlagiarismCheckType;
use App\Jobs\RunPlagiarismCheck;
use App\Models\ManuscriptSubmission;
use App\Models\PlagiarismCheck;
use App\Services\Plagiarism\OriginalityPlagiarismChecker;
use App\Services\Plagiarism\PlagiarismChecker;
use App\Services\Plagiarism\PlagiarismCheckFailed;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Sleep;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Originality.ai API v3 driver (https://docs.originality.ai): request format, result mapping,
 * chunking of long manuscripts, retries and failures.
 */
class OriginalityPlagiarismTest extends TestCase
{
    private const SCAN_URL = 'https://api.originality.ai/api/v3/scan';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
        Sleep::fake();
        Http::preventStrayRequests();
        $this->seed(NotificationTemplateSeeder::class);

        config([
            'app.url' => 'https://moslegis.example',
            'services.plagiarism.driver' => 'originality',
            'services.plagiarism.api_key' => 'test-key',
            'services.plagiarism.chunk_words' => 1500,
        ]);
    }

    /** A /scan response shaped like the documentation example. */
    private static function scanResponse(float $score, array $phrases = [], int $credits = 9): array
    {
        return ['results' => [
            'properties' => ['privateID' => 30137337, 'id' => 'scan-abc', 'title' => 'My API Scan', 'publicLink' => 'https://app.originality.ai/share/abc'],
            'credits' => ['used' => $credits],
            'plagiarism' => [
                'score' => $score,
                'results' => array_map(fn ($phrase) => [
                    'phrase' => $phrase[0],
                    'results' => [['link' => $phrase[1], 'title' => $phrase[2], 'scores' => [['score' => 1, 'sentence' => $phrase[0]]]]],
                ], $phrases),
            ],
        ]];
    }

    private static function words(int $count, string $word = 'law'): string
    {
        return trim(str_repeat($word.' ', $count));
    }

    #[Test]
    public function it_sends_a_plagiarism_only_scan_as_documented_and_maps_the_result(): void
    {
        $text = self::words(80).'. The doctrine of basic structure limits amendment. '.self::words(10);
        Http::fake([self::SCAN_URL => Http::response(self::scanResponse(12.5, [
            ['The doctrine of basic structure limits amendment.', 'https://example.org/basic-structure', 'Basic structure — Example Law Review'],
        ]))]);

        $result = app(PlagiarismChecker::class)->check('Basic Structure', $text);

        Http::assertSent(function (Request $request) use ($text) {
            return $request->url() === self::SCAN_URL
                && $request->method() === 'POST'
                && $request->hasHeader('X-OAI-API-KEY', 'test-key')
                && $request['check_plagiarism'] === true
                && $request['check_ai'] === false && $request['check_facts'] === false
                && $request['check_readability'] === false && $request['check_grammar'] === false
                && $request['storeScan'] === true
                && $request['aiModelVersion'] === 'lite'
                && $request['excludedUrls'] === ['https://moslegis.example']
                && $request['title'] === 'Basic Structure'
                && $request['content'] === $text;
        });

        $this->assertSame(12.5, $result->similarity);
        $this->assertSame('Basic structure — Example Law Review', $result->matches[0]['source']);
        $this->assertSame('https://example.org/basic-structure', $result->matches[0]['url']);
        $this->assertSame(round(7 / 97 * 100, 2), $result->matches[0]['similarity']);
        $this->assertSame('originality', $result->raw['driver']);
        $this->assertSame(9, $result->raw['credits_used']);
        $this->assertSame('https://app.originality.ai/share/abc', $result->raw['scans'][0]['public_link']);
    }

    #[Test]
    public function long_manuscripts_are_scanned_in_chunks_and_the_score_is_word_weighted(): void
    {
        // Three paragraphs: 1,400 + 1,400 + 700 words → three scans (limit 1,500 words each).
        $text = self::words(1400, 'alpha')."\n\n".self::words(1400, 'beta')."\n\n".self::words(700, 'gamma');
        Http::fake([self::SCAN_URL => Http::sequence()
            ->push(self::scanResponse(10))
            ->push(self::scanResponse(0))
            ->push(self::scanResponse(40))]);

        $result = app(PlagiarismChecker::class)->check('Long', $text);

        Http::assertSentCount(3);
        Http::assertSent(fn (Request $r) => $r['title'] === 'Long (part 2/3)' && str_starts_with($r['content'], 'beta'));
        // (10 × 1400 + 0 × 1400 + 40 × 700) / 3500 = 12
        $this->assertSame(12.0, $result->similarity);
        $this->assertSame(3500, $result->raw['words_checked']);
        $this->assertSame(27, $result->raw['credits_used']);
    }

    #[Test]
    public function chunks_respect_the_word_limit_and_merge_a_short_tail(): void
    {
        $chunks = OriginalityPlagiarismChecker::chunks(self::words(3000).' end.', 1500);
        $this->assertCount(2, $chunks); // a 1-word tail is merged into the previous chunk

        $chunks = OriginalityPlagiarismChecker::chunks(self::words(400)."\n".self::words(400)."\n".self::words(400), 500);
        $this->assertCount(3, $chunks);
        $this->assertSame([], OriginalityPlagiarismChecker::chunks("  \n ", 500));
    }

    #[Test]
    public function a_rate_limited_request_is_retried(): void
    {
        Http::fake([self::SCAN_URL => Http::sequence()
            ->push(['error' => 'Too many requests'], 429, ['Retry-After' => '1'])
            ->push(self::scanResponse(3))]);

        $this->assertSame(3.0, app(PlagiarismChecker::class)->check('T', self::words(300))->similarity);
        Http::assertSentCount(2);
    }

    #[Test]
    public function a_retried_job_does_not_pay_for_the_same_text_twice(): void
    {
        Http::fake([self::SCAN_URL => Http::response(self::scanResponse(5))]);
        $checker = app(PlagiarismChecker::class);

        $checker->check('T', self::words(300));
        $checker->check('T', self::words(300));

        Http::assertSentCount(1);
    }

    #[Test]
    public function a_rejected_api_key_fails_the_check_at_once_with_the_reason(): void
    {
        Http::fake([self::SCAN_URL => Http::response(['error' => 'Invalid API key'], 401)]);
        $check = PlagiarismCheck::factory()->create(['content' => self::words(300)]);

        try {
            app(PlagiarismChecker::class)->check('T', self::words(300));
            $this->fail('Expected a failure');
        } catch (PlagiarismCheckFailed $e) {
            $this->assertFalse($e->retryable);
            $this->assertStringContainsString('rejected the API key', $e->getMessage());
        }

        RunPlagiarismCheck::dispatchSync($check);

        $check->refresh();
        $this->assertSame(PlagiarismCheckStatus::Failed, $check->check_status);
        $this->assertStringContainsString('Invalid API key', $check->api_response['error']);
        $this->actingAs($this->superadmin())->get(route('admin.plagiarism-checks.show', $check))
            ->assertOk()->assertSee('Check failed:')->assertSee('Invalid API key');
    }

    #[Test]
    public function a_missing_api_key_fails_without_calling_the_api(): void
    {
        config(['services.plagiarism.api_key' => null]);
        Http::fake();

        $this->expectException(PlagiarismCheckFailed::class);
        $this->expectExceptionMessage('PLAGIARISM_API_KEY');
        app(PlagiarismChecker::class)->check('T', self::words(300));
    }

    #[Test]
    public function a_manuscript_check_runs_end_to_end_and_applies_the_threshold(): void
    {
        Http::fake([self::SCAN_URL => Http::response(self::scanResponse(4.2, [
            ['Some copied sentence here.', 'https://example.org/a', 'Source A'],
        ]))]);
        $submission = ManuscriptSubmission::factory()->stage(ManuscriptStage::Pending)->create();
        $check = PlagiarismCheck::factory()->create([
            'user_id' => $submission->user_id,
            'manuscript_submission_id' => $submission->id,
            'check_type' => PlagiarismCheckType::Manuscript,
            'content' => self::words(500).' Some copied sentence here.',
        ]);

        RunPlagiarismCheck::dispatchSync($check);

        $check->refresh();
        $this->assertSame(PlagiarismCheckStatus::Completed, $check->check_status);
        $this->assertEquals(4.2, (float) $check->similarity_percentage);
        $this->assertSame('https://example.org/a', $check->api_response['matches'][0]['url']);
        $this->assertSame(ManuscriptStage::PlagiarismAccepted, $submission->fresh()->stage);
        Storage::disk('local')->assertExists($check->report_file);

        $this->actingAs($this->superadmin())->get(route('admin.plagiarism-checks.show', $check))->assertOk()
            ->assertSee('Checked by Originality.ai')->assertSee('https://example.org/a')->assertSee('Scan on Originality.ai');
    }

    #[Test]
    public function the_admin_list_shows_the_remaining_credits(): void
    {
        Http::fake(['https://api.originality.ai/api/v3/account/balance' => Http::response(['credits' => 8646, 'subscriptionCredits' => 1745])]);

        $this->actingAs($this->superadmin())->get(route('admin.plagiarism-checks.index'))->assertOk()
            ->assertSee('Checker: Originality.ai')->assertSee('10,391');
        Http::assertSent(fn (Request $r) => $r->hasHeader('X-OAI-API-KEY', 'test-key') && $r->method() === 'GET');
    }

    #[Test]
    public function the_fake_driver_still_works_for_local_development(): void
    {
        config(['services.plagiarism.driver' => 'fake', 'services.plagiarism.fake_similarity' => '2']);

        $this->assertSame(2.0, app(PlagiarismChecker::class)->check('T', 'text')->similarity);
        $this->actingAs($this->superadmin())->get(route('admin.plagiarism-checks.index'))->assertOk()->assertSee('simulated results');
    }
}
