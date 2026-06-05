<?php

namespace App\Services;

use App\Models\Booking;

class BookingPaymentService
{
    public const MANUAL_METHODS = ['cash', 'cheque'];

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
