<?php

namespace App\Services\Payments;

use App\Models\Payment;

interface PaymentGateway
{
    /** "razorpay" or "simulated". */
    public function name(): string;

    /** Create a gateway order for the payment and return its id. */
    public function createOrder(Payment $payment): string;

    /** Options for the browser checkout widget. */
    public function checkoutOptions(Payment $payment): array;

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool;

    /** @return array{method: ?string, details: ?string} */
    public function paymentDetails(string $paymentId): array;

    public function verifyWebhook(string $payload, string $signature): bool;

    /**
     * What the gateway knows about the payment's order right now (the source of truth after checkout):
     * paid = an attempt was captured for the full amount; processing = an attempt is still in progress;
     * failed = every attempt failed; none = no attempt yet; unknown = the gateway could not be reached.
     *
     * @return array{state: 'paid'|'processing'|'failed'|'none'|'unknown', payment_id: ?string, method: ?string, details: ?string, reason: ?string}
     */
    public function orderStatus(Payment $payment): array;
}
