<?php

namespace App\Services;

use App\Models\Booking;

class InvoiceService
{
    /**
     * @return array<string, mixed>
     */
    public function getInvoiceData(int $bookingId): array
    {
        $booking = Booking::query()
            ->with(['user.details', 'property', 'items.cabin', 'items.seat', 'payments'])
            ->findOrFail($bookingId);

        $subtotal = (float) $booking->subtotal_amount;
        $grandTotal = (float) $booking->grand_total_amount;
        $totalPaid = BookingPaymentService::totalPaid($booking);
        $balance = BookingPaymentService::remainingAmount($booking);

        $taxBreakdown = $this->buildTaxBreakdown($booking);

        $latestPayment = $booking->payments
            ->where('payment_status', 'paid')
            ->sortByDesc(fn ($payment) => $payment->paid_at ?? $payment->created_at)
            ->first();

        return [
            'company' => $this->buildCompanyData(),
            'customer' => $this->buildCustomerData($booking),
            'booking' => [
                'id' => $booking->id,
                'display_id' => formatBookingId($booking->id),
                'invoice_no' => $booking->invoice_no,
                'date' => $booking->created_at,
                //'due_date' => $booking->end_datetime,
                'status' => $booking->payment_status,
                'property' => $booking->property?->name,
                'cabins_and_seats' => $booking->cabinsAndSeatsLines(),
                'duration' => [
                    formatDate($booking->start_datetime),
                    formatDate($booking->end_datetime),
                ],
                'duration_type' => humanize($booking->duration_type),
            ],
            'items' => $this->buildLineItems($booking),
            'payment' => array_merge([
                'subtotal' => $subtotal,
                'total' => $grandTotal,
                'amount_paid' => $totalPaid,
                'balance' => $balance,
                'method' => $latestPayment?->payment_method,
                'paid_at' => $latestPayment?->paid_at,
            ], $taxBreakdown),
            'bank' => [
                'account_name' => get_setting('bank_account_holder_name'),
                'bank_name' => get_setting('bank_name'),
                'account_number' => get_setting('bank_account_number'),
                'ifsc' => get_setting('bank_ifsc'),
                'branch' => get_setting('bank_branch'),
            ],
            'notes' => get_setting('invoice_notes', ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCompanyData(): array
    {
        return [
            'name' => get_setting('name'),
            'address' => get_setting('address'),
            'logo' => backend_logo_url(),
            'email' => get_setting('email'),
            'phone' => get_setting('phone'),
            'website' => get_setting('website'),
            'gstin' => get_setting('gstin'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildCustomerData(Booking $booking): array
    {
        $user = $booking->user;
        $addressParts = array_filter([
            $user?->detailValue('street_address'),
            $user?->detailValue('city'),
            $user?->detailValue('state'),
            $user?->detailValue('pin_code'),
            $user?->detailValue('country'),
        ]);

        return [
            'name' => $user?->name,
            'company_name' => $user?->detailValue('company_name'),
            'state' => $user?->detailValue('state') ?: null,
            'address' => $addressParts ? implode(', ', $addressParts) : null,
            'email' => $user?->email,
            'phone' => $user?->phone,
            'gstin' => $user?->detailValue('gstin'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildTaxBreakdown(Booking $booking): array
    {
        $taxRate = (float) $booking->tax_rate;
        $taxAmount = (float) $booking->tax_amount;

        if ($this->isMaharashtraState($booking->user?->detailValue('state'))) {
            $halfRate = round($taxRate / 2, 2);
            $cgstAmount = round($taxAmount / 2, 2);
            $sgstAmount = round($taxAmount - $cgstAmount, 2);

            return [
                'tax_type' => 'cgst_sgst',
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'cgst_rate' => $halfRate,
                'cgst_amount' => $cgstAmount,
                'sgst_rate' => $halfRate,
                'sgst_amount' => $sgstAmount,
            ];
        }

        return [
            'tax_type' => 'igst',
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'igst_rate' => $taxRate,
            'igst_amount' => $taxAmount,
        ];
    }

    private function isMaharashtraState(?string $state): bool
    {
        if ($state === null || trim($state) === '') {
            return false;
        }

        return in_array(strtolower(trim($state)), ['maharashtra', 'mh'], true);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildLineItems(Booking $booking): array
    {
        $hsn = get_setting('invoice_hsn_code', '997212');
        $propertyName = $booking->property?->name;
        $subtitle = trim(
            humanize($booking->duration_type ?? '').' — '
            .formatDate($booking->start_datetime).' – '.formatDate($booking->end_datetime)
        );

        $items = [];

        foreach ($booking->items->groupBy('cabin_id') as $cabinItems) {
            $cabinName = $cabinItems->first()->cabin?->name ?? '—';
            $seatNos = $cabinItems
                ->map(fn ($item) => $item->seat?->seat_no)
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');

            $title = $propertyName ? $cabinName.' — '.$propertyName : $cabinName;
            $description = $seatNos ? $title.' — '.$seatNos : $title;

            $qty = $cabinItems->count();
            $amount = round((float) $cabinItems->sum(fn ($item) => (float) $item->amount), 2);
            $rate = $qty > 0 ? round($amount / $qty, 2) : 0.0;

            $items[] = [
                'description' => $description,
                'subtitle' => $subtitle,
                'hsn' => $hsn,
                'qty' => $qty,
                'rate' => $rate,
                'amount' => $amount,
            ];
        }

        return $items;
    }
}
