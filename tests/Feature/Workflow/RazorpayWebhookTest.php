<?php

namespace Tests\Feature\Workflow;

use App\Enums\PaymentStatus;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\SimulatedGateway;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

class RazorpayWebhookTest extends TestCase
{
    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
        $this->seed(NotificationTemplateSeeder::class);

        $this->app->bind(PaymentGateway::class, fn () => new class extends SimulatedGateway
        {
            public function verifyWebhook(string $payload, string $signature): bool
            {
                return $signature === 'valid';
            }
        });

        $submission = ManuscriptSubmission::factory()->create(['manuscript_attachment' => Docx::withWords(50)->store('manuscripts', 'local')]);
        $this->payment = Payment::factory()->create(['payable_id' => $submission->id, 'gateway_order_id' => 'order_123', 'amount' => 150, 'tax_amount' => 27]);
    }

    private function captured(int $amount, string $signature = 'valid'): TestResponse
    {
        return $this->postJson(route('payments.razorpay.webhook'), [
            'event' => 'payment.captured',
            'payload' => ['payment' => ['entity' => ['id' => 'pay_abc', 'order_id' => 'order_123', 'amount' => $amount, 'method' => 'card']]],
        ], ['X-Razorpay-Signature' => $signature]);
    }

    #[Test]
    public function captured_webhook_settles_the_payment_once(): void
    {
        $this->captured(17700)->assertOk();
        $this->captured(17700)->assertOk();

        $this->assertSame(PaymentStatus::Paid, $this->payment->fresh()->payment_status);
        $this->assertSame(1, $this->payment->payable->plagiarismChecks()->count());
    }

    #[Test]
    public function amount_mismatch_is_rejected(): void
    {
        $this->captured(100)->assertStatus(422);

        $this->assertSame(PaymentStatus::Pending, $this->payment->fresh()->payment_status);
    }

    #[Test]
    public function invalid_signature_is_rejected(): void
    {
        $this->captured(17700, 'forged')->assertStatus(400);

        $this->assertSame(PaymentStatus::Pending, $this->payment->fresh()->payment_status);
    }
}
