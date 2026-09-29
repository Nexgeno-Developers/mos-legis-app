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
            $payment = $this->api->payment->fetch($paymentId);
        } catch (Throwable) {
            return ['method' => null, 'details' => null];
        }

        $details = match ($payment['method'] ?? null) {
            'card' => isset($payment['card']['last4']) ? 'Card ending '.$payment['card']['last4'] : 'Card',
            'upi' => 'UPI '.($payment['vpa'] ?? ''),
            'netbanking' => 'Netbanking '.($payment['bank'] ?? ''),
            'wallet' => 'Wallet '.($payment['wallet'] ?? ''),
            default => null,
        };

        return ['method' => $payment['method'] ?? null, 'details' => $details ? trim($details) : null];
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
