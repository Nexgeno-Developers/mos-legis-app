<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Throwable;

/**
 * Razorpay Standard Checkout (SOW Inclusions). Amounts are sent in paise; INR only.
 */
class RazorpayGateway implements PaymentGateway
{
    private Api $api;

    public function __construct(private readonly string $keyId, string $keySecret, private readonly ?string $webhookSecret)
    {
        $this->api = new Api($keyId, $keySecret);
    }

    public function name(): string
    {
        return 'razorpay';
    }

    public function createOrder(Payment $payment): string
    {
        $order = $this->api->order->create([
            'receipt' => 'payment_'.$payment->id,
            'amount' => $payment->totalInMinorUnits(),
            'currency' => $payment->currency,
            'notes' => ['payment_id' => (string) $payment->id, 'purpose' => $payment->payment_purpose->value],
        ]);

        return $order['id'];
    }

    public function checkoutOptions(Payment $payment): array
    {
        return [
            'key' => $this->keyId,
            'amount' => $payment->totalInMinorUnits(),
            'currency' => $payment->currency,
            'order_id' => $payment->gateway_order_id,
            'name' => settings('general.application_name'),
            'description' => $payment->payment_purpose->label().' fee',
            'prefill' => ['name' => $payment->user->name, 'email' => $payment->user->email, 'contact' => (string) $payment->user->phone],
            'theme' => ['color' => '#B01B25'],
        ];
    }

    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }

    public function paymentDetails(string $paymentId): array
    {
        try {
            $payment = $this->api->payment->fetch($paymentId)->toArray();
        } catch (Throwable) {
            return ['method' => null, 'details' => null];
        }

        return ['method' => $payment['method'] ?? null, 'details' => $this->describe($payment)];
    }

    public function orderStatus(Payment $payment): array
    {
        $result = ['state' => 'none', 'payment_id' => null, 'method' => null, 'details' => null, 'reason' => null];

        if (! $payment->gateway_order_id) {
            return $result;
        }

        try {
            $attempts = $this->api->order->fetch($payment->gateway_order_id)->payments()->toArray()['items'] ?? [];
        } catch (Throwable) {
            return ['state' => 'unknown'] + $result;
        }

        // Newest attempt first.
        usort($attempts, fn ($a, $b) => ($b['created_at'] ?? 0) <=> ($a['created_at'] ?? 0));
        $expected = $payment->totalInMinorUnits();

        foreach ($attempts as $attempt) {
            if ((int) ($attempt['amount'] ?? 0) !== $expected) {
                continue;
            }

            // Authorised but not auto-captured (e.g. a late UPI confirmation): capture it now.
            if (($attempt['status'] ?? null) === 'authorized') {
                try {
                    $attempt = $this->api->payment->fetch($attempt['id'])->capture(['amount' => $expected, 'currency' => $payment->currency])->toArray();
                } catch (Throwable) {
                    return ['state' => 'processing'] + $result;
                }
            }

            if (($attempt['status'] ?? null) === 'captured') {
                return ['state' => 'paid', 'payment_id' => $attempt['id'], 'method' => $attempt['method'] ?? null, 'details' => $this->describe($attempt), 'reason' => null];
            }
        }

        if ($attempts === []) {
            return $result;
        }

        if (collect($attempts)->contains(fn ($a) => in_array($a['status'] ?? null, ['created', 'authorized'], true))) {
            return ['state' => 'processing'] + $result;
        }

        return ['state' => 'failed', 'reason' => $attempts[0]['error_description'] ?? null] + $result;
    }

    private function describe(array $payment): ?string
    {
        $details = match ($payment['method'] ?? null) {
            'card' => isset($payment['card']['last4']) ? 'Card ending '.$payment['card']['last4'] : 'Card',
            'upi' => 'UPI '.($payment['vpa'] ?? ''),
            'netbanking' => 'Netbanking '.($payment['bank'] ?? ''),
            'wallet' => 'Wallet '.($payment['wallet'] ?? ''),
            default => null,
        };

        return $details ? trim($details) : null;
    }

    public function verifyWebhook(string $payload, string $signature): bool
    {
        if (! $this->webhookSecret) {
            return false;
        }

        try {
            $this->api->utility->verifyWebhookSignature($payload, $signature, $this->webhookSecret);

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }
}
