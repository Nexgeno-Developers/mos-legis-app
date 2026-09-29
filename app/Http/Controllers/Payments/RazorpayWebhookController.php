<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentGateway;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Razorpay webhook (payment.captured / payment.failed). Verified by signature,
 * so a closed browser tab never leaves a paid manuscript unprocessed.
 */
class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentGateway $gateway, PaymentService $payments): JsonResponse
    {
        if (! $gateway->verifyWebhook($request->getContent(), (string) $request->header('X-Razorpay-Signature'))) {
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $entity = $request->input('payload.payment.entity', []);
        $payment = isset($entity['order_id']) ? Payment::firstWhere('gateway_order_id', $entity['order_id']) : null;

        if (! $payment) {
            return response()->json(['status' => 'ignored']);
        }

        // Never settle a payment for a different amount than was charged.
        if ($request->input('event') === 'payment.captured' && (int) ($entity['amount'] ?? 0) !== $payment->totalInMinorUnits()) {
            activity()->log('Payments', 'Webhook amount mismatch', $payment, ['captured' => $entity['amount'] ?? null]);

            return response()->json(['status' => 'amount mismatch'], 422);
        }

        match ($request->input('event')) {
            'payment.captured' => $payments->markPaid($payment, $entity['id'], $entity['method'] ?? null, null),
            'payment.failed' => $payments->markFailed($payment, $entity['error_description'] ?? 'Payment failed'),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }
}
