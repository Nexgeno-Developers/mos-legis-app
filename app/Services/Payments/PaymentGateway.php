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
}
