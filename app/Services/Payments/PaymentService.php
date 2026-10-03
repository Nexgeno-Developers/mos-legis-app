<?php

namespace App\Services\Payments;

use App\Enums\PaymentPurpose;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Payment;
use App\Models\User;
use App\Notifications\WorkflowNotifier;
use App\Services\Manuscripts\FeeCalculator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Creates payments with a frozen billing snapshot and tax (clarification #5),
 * and settles them exactly once (callback and webhook may both arrive).
 */
class PaymentService
{
    public function __construct(
        private readonly FeeCalculator $fees,
        private readonly PaymentFulfillment $fulfillment,
        private readonly WorkflowNotifier $notifier,
    ) {}

    /** Re-uses the open pending payment for the same charge so retries don't pile up rows. */
    public function createPending(User $user, Model $payable, PaymentPurpose $purpose, float $amount, Address $address): Payment
    {
        $quote = $this->fees->withTax($amount, $address->country_code);

        return Payment::updateOrCreate(
            [
                'user_id' => $user->id,
                'payable_type' => $payable->getMorphClass(),
                'payable_id' => $payable->getKey(),
                'payment_purpose' => $purpose,
                'payment_status' => PaymentStatus::Pending,
            ],
            [
                // Inclusive pricing: taxable value + tax = the fee the payer is charged.
                'amount' => $quote['base'],
                'tax_amount' => $quote['tax'],
                'tax_rate' => $quote['rate'],
                // Within the business state: CGST + SGST; another Indian state: IGST.
                'gst_type' => $quote['tax'] > 0
                    ? (strcasecmp(trim((string) $address->state), trim((string) settings('payment.business_state', 'Maharashtra'))) === 0 ? 'intra' : 'inter')
                    : null,
                'currency' => settings('payment.currency', 'INR'),
                'billing_address_id' => $address->id,
                'billing_country_code' => strtoupper($address->country_code),
                'billing_details' => $address->toBillingSnapshot(),
                'gateway_order_id' => null,
            ],
        );
    }

    public function markPaid(Payment $payment, string $gatewayPaymentId, ?string $method = null, ?string $details = null): Payment
    {
        $settled = DB::transaction(function () use ($payment, $gatewayPaymentId, $method, $details) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->isPaid()) {
                return null;
            }

            $locked->forceFill([
                'payment_status' => PaymentStatus::Paid,
                'payment_id' => $gatewayPaymentId,
                'payment_method' => $method ?? 'online',
                'payment_details' => $details,
                'paid_at' => now(),
                'invoice_number' => sprintf('MOS-INV-%d-%06d', now()->year, $locked->id),
            ])->save();

            activity()->log('Payments', 'Payment received', $locked, [
                'purpose' => $locked->payment_purpose->value, 'total' => (string) $locked->total_amount, 'gateway_payment_id' => $gatewayPaymentId,
            ]);

            $this->fulfillment->fulfill($locked);

            return $locked;
        });

        if ($settled) {
            $data = [
                'amount' => money($settled->total_amount),
                'purpose' => $settled->payment_purpose->label(),
                'invoice_number' => $settled->invoice_number,
            ];
            $this->notifier->toUser('payment_received', $settled->user, $data);
            $this->notifier->toAdmins('payment_received', $data);
        }

        return $settled ?? $payment->fresh();
    }

    public function markFailed(Payment $payment, ?string $reason = null): void
    {
        if ($payment->isPaid()) {
            return;
        }

        $payment->forceFill(['payment_status' => PaymentStatus::Failed, 'remarks' => $reason ? mb_substr($reason, 0, 255) : null])->save();
        activity()->log('Payments', 'Payment failed', $payment, [], $reason);
    }
}
