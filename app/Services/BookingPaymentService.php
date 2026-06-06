<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class BookingPaymentService
{
    public const MANUAL_METHODS = ['cash', 'cheque', 'bank_transfer'];

    public static function totalPaid(Booking $booking): float
    {
        if ($booking->relationLoaded('payments')) {
            return (float) $booking->payments
                ->where('payment_status', 'paid')
                ->sum('amount');
        }

        return (float) $booking->payments()
            ->where('payment_status', 'paid')
            ->sum('amount');
    }

    public static function remainingAmount(Booking $booking): float
    {
        return max(0, round((float) $booking->grand_total_amount - self::totalPaid($booking), 2));
    }

    public static function canAcceptPayment(Booking $booking, float $amount): bool
    {
        return round(self::totalPaid($booking) + $amount, 2) <= round((float) $booking->grand_total_amount, 2);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function buildPaymentDetails(string $method, array $data): array
    {
        return match ($method) {
            'cash' => [
                'receipt_number' => $data['receipt_number'],
                'received_by' => $data['received_by'],
            ],
            'cheque' => [
                'cheque_number' => $data['cheque_number'],
                'bank_name' => $data['bank_name'],
                'cheque_date' => $data['cheque_date'],
            ],
            'bank_transfer' => [
                'utr_no' => $data['utr_no'],
                'bank_name' => $data['bank_name'],
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function createManualPayment(Booking $booking, array $validated): Payment
    {
        return DB::transaction(function () use ($booking, $validated) {
            $method = $validated['payment_method'];

            $payment = Payment::create([
                'user_id' => $booking->user_id,
                'payable_type' => 'booking',
                'payable_id' => $booking->id,
                'amount' => $validated['amount'],
                'payment_method' => $method,
                'payment_status' => 'paid',
                'payment_details' => self::buildPaymentDetails($method, $validated),
                'payment_id' => generatePaymentId(),
                'remarks' => $validated['remarks'] ?? null,
                'paid_at' => $validated['paid_at'],
            ]);

            self::syncPaymentStatus($booking->fresh(['payments']));

            return $payment;
        });
    }

    public static function deleteManualPayment(Booking $booking, Payment $payment): void
    {
        DB::transaction(function () use ($booking, $payment) {
            $payment->delete();
            self::syncPaymentStatus($booking->fresh(['payments']));
        });
    }

    public static function syncPaymentStatus(Booking $booking): void
    {
        $totalPaid = self::totalPaid($booking);
        $grandTotal = (float) $booking->grand_total_amount;

        if ($totalPaid <= 0) {
            $status = 'unpaid';
        } elseif ($totalPaid >= $grandTotal) {
            $status = 'paid';
        } else {
            $status = 'partially_paid';
        }

        $booking->update(['payment_status' => $status]);
    }
}
