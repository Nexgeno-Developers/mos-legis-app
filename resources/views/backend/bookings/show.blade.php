@php
    $bookingBadgeClass = match ($booking->booking_status) {
        'active' => 'bg-success',
        'completed' => 'bg-info',
        'cancelled' => 'bg-danger',
        default => 'bg-warning',
    };

    $paymentBadgeClass = match ($booking->payment_status) {
        'paid' => 'bg-success',
        'partially_paid' => 'bg-warning',
        default => 'bg-danger',
    };
@endphp

<div class="row g-3">
    <div class="col-lg-6">
        <h6 class="text-uppercase fw-bold mb-2">{{ __('labels.booking_details') }}</h6>
        <table class="table table-sm table-bordered mb-0">
            <tbody>
                <tr>
                    <th class="bg-light" style="width: 40%;">{{ __('labels.booking_id') }}</th>
                    <td>#{{ $booking->id }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.invoice_number') }}</th>
                    <td>{{ $booking->invoice_no }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.property') }}</th>
                    <td>{{ $booking->property?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.duration_type') }}</th>
                    <td>{{ ucfirst($booking->duration_type ?? '—') }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.duration') }}</th>
                    <td>{{ formatDate($booking->start_datetime) }} – {{ formatDate($booking->end_datetime) }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.booking_status') }}</th>
                    <td><span class="badge {{ $bookingBadgeClass }}">{{ ucfirst($booking->booking_status) }}</span></td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.payment_status') }}</th>
                    <td><span class="badge {{ $paymentBadgeClass }}">{{ ucfirst(str_replace('_', ' ', $booking->payment_status)) }}</span></td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.created') }}</th>
                    <td>{{ formatDatetime($booking->created_at) }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.updated') }}</th>
                    <td>{{ formatDatetime($booking->updated_at) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-lg-6">
        <h6 class="text-uppercase fw-bold mb-2">{{ __('labels.customer') }}</h6>
        <table class="table table-sm table-bordered mb-0">
            <tbody>
                <tr>
                    <th class="bg-light" style="width: 40%;">{{ __('labels.name') }}</th>
                    <td>{{ $booking->user?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.email') }}</th>
                    <td>{{ $booking->user?->email ?? '—' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.phone') }}</th>
                    <td>{{ $booking->user?->phone ?? '—' }}</td>
                </tr>
            </tbody>
        </table>

        <h6 class="text-uppercase fw-bold mb-2 mt-3">{{ __('labels.amount_summary') }}</h6>
        <table class="table table-sm table-bordered mb-0">
            <tbody>
                <tr>
                    <th class="bg-light" style="width: 40%;">{{ __('labels.subtotal') }}</th>
                    <td>{{ formatCurrency($booking->subtotal_amount) }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.tax_rate') }}</th>
                    <td>{{ number_format((float) $booking->tax_rate, 2) }}%</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.tax_amount') }}</th>
                    <td>{{ formatCurrency($booking->tax_amount) }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.grand_total') }}</th>
                    <td class="fw-bold">{{ formatCurrency($booking->grand_total_amount) }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <h6 class="text-uppercase fw-bold mb-2">{{ __('labels.booking_items') }}</h6>
    <div class="table-responsive">
        <table class="table table-sm table-striped table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('labels.#') }}</th>
                    <th>{{ __('labels.cabin') }}</th>
                    <th>{{ __('labels.seat_no') }}</th>
                    <th>{{ __('labels.occupant_name') }}</th>
                    <th>{{ __('labels.occupant_phone') }}</th>
                    <th>{{ __('labels.id_proof') }}</th>
                    <th>{{ __('labels.kyc_status') }}</th>
                    <th class="text-end">{{ __('labels.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($booking->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->cabin?->name ?? '—' }}</td>
                    <td>{{ $item->seat?->seat_no ?? '—' }}</td>
                    <td>{{ $item->occupant_name ?? '—' }}</td>
                    <td>{{ $item->occupant_phone ?? '—' }}</td>
                    <td>{{ $item->occupant_id_proof_no ?? '—' }}</td>
                    <td>
                        <span class="badge {{ $item->kyc_status === 'verified' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($item->kyc_status) }}
                        </span>
                    </td>
                    <td class="text-end">{{ formatCurrency($item->amount) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">{{ __('labels.no_records') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    <h6 class="text-uppercase fw-bold mb-2">{{ __('labels.payments') }}</h6>
    <div class="table-responsive">
        <table class="table table-sm table-striped table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('labels.#') }}</th>
                    <th>{{ __('labels.payment_id') }}</th>
                    <th>{{ __('labels.payment_method') }}</th>
                    <th>{{ __('labels.payment_status') }}</th>
                    <th class="text-end">{{ __('labels.amount') }}</th>
                    <th>{{ __('labels.paid_at') }}</th>
                    <th>{{ __('labels.remarks') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($booking->payments as $index => $payment)
                @php
                    $itemPaymentBadgeClass = match ($payment->payment_status) {
                        'paid' => 'bg-success',
                        'processing' => 'bg-info',
                        'refunded' => 'bg-secondary',
                        'failed' => 'bg-danger',
                        default => 'bg-warning',
                    };
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $payment->payment_id ?? '—' }}</td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td>
                        <span class="badge {{ $itemPaymentBadgeClass }}">
                            {{ ucfirst($payment->payment_status) }}
                        </span>
                    </td>
                    <td class="text-end">{{ formatCurrency($payment->amount) }}</td>
                    <td>{{ $payment->paid_at ? formatDatetime($payment->paid_at) : '—' }}</td>
                    <td>{{ $payment->remarks ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">{{ __('labels.no_records') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
