<?php

namespace Tests\Feature\Workflow;

use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Models\ManuscriptSubmission;
use App\Models\Payment;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentService;
use App\Services\Payments\SimulatedGateway;
use Database\Seeders\NotificationTemplateSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\Support\Docx;
use Tests\TestCase;

/**
 * After checkout the status page asks the gateway for the outcome, so a payment the widget
 * reported as failed but the bank confirmed seconds later still ends as a success.
 */
class PaymentStatusTest extends TestCase
{
    /** What the fake gateway reports for the order. */
    public static string $gatewayState = 'failed';

    private Payment $payment;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
        Cache::flush();
        $this->seed(NotificationTemplateSeeder::class);
        self::$gatewayState = 'failed';

        $this->app->bind(PaymentGateway::class, fn () => new class extends SimulatedGateway
        {
            public function orderStatus(Payment $payment): array
            {
                return [
                    'state' => PaymentStatusTest::$gatewayState,
                    'payment_id' => PaymentStatusTest::$gatewayState === 'paid' ? 'pay_late123' : null,
                    'method' => 'upi', 'details' => 'UPI asha@okbank',
                    'reason' => PaymentStatusTest::$gatewayState === 'failed' ? 'Payment was declined by the bank' : null,
                ];
            }
        });

        $submission = ManuscriptSubmission::factory()->create(['user_id' => $this->author()->id, 'manuscript_attachment' => Docx::withWords(50)->store('manuscripts', 'local')]);
        $this->payment = Payment::factory()->create(['payable_id' => $submission->id, 'gateway_order_id' => 'order_123'])->fresh();
    }

    private function statusPage(array $query = [])
    {
        return $this->actingAs($this->payment->user)->get(route('account.payments.status', [$this->payment] + $query));
    }

    #[Test]
    public function the_page_keeps_checking_before_showing_a_failure(): void
    {
        $this->statusPage()->assertOk()->assertSee('Checking with your bank')->assertDontSee('didn\'t go through', false);

        $this->statusPage(['final' => 1])->assertOk()->assertSee('Your payment didn')->assertSee('Payment was declined by the bank')
            ->assertSee(route('account.payments.pay', $this->payment), false);
    }

    #[Test]
    public function a_late_bank_confirmation_turns_into_a_success(): void
    {
        $this->actingAs($this->payment->user)->getJson(route('account.payments.check', $this->payment))->assertJson(['state' => 'failed']);

        self::$gatewayState = 'paid';
        Cache::flush();

        $this->actingAs($this->payment->user)->getJson(route('account.payments.check', $this->payment))->assertJson(['state' => 'paid']);
        $this->assertSame(PaymentStatus::Paid, $this->payment->fresh()->payment_status);
        $this->assertSame('pay_late123', $this->payment->fresh()->payment_id);

        $this->statusPage()->assertSee('Payment successful')->assertSee('UPI asha@okbank')->assertSee($this->payment->fresh()->invoice_number);
    }

    #[Test]
    public function an_attempt_still_in_progress_does_not_invite_a_second_payment(): void
    {
        self::$gatewayState = 'processing';

        $this->statusPage(['final' => 1])->assertSee('still waiting for your bank')->assertSee('pay again')
            ->assertDontSee('Try again');
    }

    #[Test]
    public function retrying_reuses_the_same_gateway_order(): void
    {
        $this->payment->forceFill(['payment_status' => PaymentStatus::Failed])->save();
        $user = $this->payment->user;
        $address = $user->address()->create(['recipient_name' => 'A', 'address_line1' => '1 Road', 'city' => 'Pune', 'state' => 'Maharashtra', 'country_code' => 'IN']);

        $retry = app(PaymentService::class)->createPending($user, $this->payment->payable, PaymentPurpose::Prescreening, (float) $this->payment->total_amount, $address);

        $this->assertSame($this->payment->id, $retry->id);
        $this->assertSame('order_123', $retry->gateway_order_id);
        $this->assertSame(PaymentStatus::Pending, $retry->payment_status);
        $this->assertSame(1, Payment::count());
    }

    #[Test]
    public function only_the_payer_can_see_the_status(): void
    {
        $this->actingAs($this->author())->get(route('account.payments.status', $this->payment))->assertForbidden();
    }
}
