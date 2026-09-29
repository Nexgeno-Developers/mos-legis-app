<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Support\Str;

/**
 * Used when no Razorpay keys are configured (local development and tests):
 * the checkout page shows a "Simulate successful payment" button instead of
 * the Razorpay widget. Never used in production.
 */
class SimulatedGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'simulated';
    }

    public function createOrder(Payment $payment): string
    {
        return 'order_sim_'.Str::lower(Str::random(14));
    }

    public function checkoutOptions(Payment $payment): array
    {
        return [];
    }

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        return hash_equals($this->signature($orderId, $paymentId), $signature);
    }

    public function paymentDetails(string $paymentId): array
    {
        return ['method' => 'upi', 'details' => 'Simulated payment'];
    }

    public function verifyWebhook(string $payload, string $signature): bool
    {
        return false;
    }

    public function signature(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId.'|'.$paymentId, (string) config('app.key'));
    }
}
