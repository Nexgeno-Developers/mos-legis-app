@php
    use App\Services\BookingPaymentService;

    $totalPaid = BookingPaymentService::totalPaid($booking);
    $remainingBalance = BookingPaymentService::remainingAmount($booking);

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

    $paymentsTableCols = auth()->user()?->can('bookings edit') ? 9 : 8;
@endphp

<div class="row g-3">
    <div class="col-lg-6">
        <h6 class="text-uppercase fw-bold mb-2">{{ __('labels.booking_details') }}</h6>
        <table class="table table-sm table-bordered mb-0">
            <tbody>
                <tr>
                    <th class="bg-light" style="width: 40%;">{{ __('labels.booking_id') }}</th>
                    <td>{{ formatBookingId($booking->id) }}</td>
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
                    <td>{{ $booking->duration_type ? humanize($booking->duration_type) : '—' }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.duration') }}</th>
                    <td>{{ formatDate($booking->start_datetime) }} – {{ formatDate($booking->end_datetime) }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.booking_status') }}</th>
                    <td><span class="badge {{ $bookingBadgeClass }}">{{ humanize($booking->booking_status) }}</span></td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.payment_status') }}</th>
                    <td><span class="badge {{ $paymentBadgeClass }}">{{ humanize($booking->payment_status) }}</span></td>
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
                <tr>
                    <th class="bg-light">{{ __('labels.total_paid') }}</th>
                    <td class="text-success">{{ formatCurrency($totalPaid) }}</td>
                </tr>
                <tr>
                    <th class="bg-light">{{ __('labels.remaining_balance') }}</th>
                    <td class="fw-bold {{ $remainingBalance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ formatCurrency($remainingBalance) }}
                    </td>
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
                            {{ humanize($item->kyc_status) }}
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
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="text-uppercase fw-bold mb-0">{{ __('labels.payments') }}</h6>
        @can('bookings edit')
        @if ($remainingBalance > 0)
        <button type="button" class="btn btn-sm btn-primary" id="togglePaymentForm">
            <i class="ti ti-plus"></i> {{ __('labels.add_payment') }}
        </button>
        @endif
        @endcan
    </div>

    @can('bookings edit')
    @if ($remainingBalance > 0)
    <div id="paymentFormWrapper" class="card border mb-3 d-none">
        <div class="card-body">
            <form class="form" id="addPaymentForm" action="{{ route('bookings.payments.store', $booking->id) }}" method="POST">
                @csrf
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.amount') }} <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="{{ $remainingBalance }}" required>
                            <small class="text-muted">{{ __('labels.max_payable') }}: {{ formatCurrency($remainingBalance) }}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.payment_method') }} <span class="text-danger">*</span></label>
                            <select name="payment_method" id="paymentMethod" class="form-select" required>
                                <option value="">{{ __('labels.select_payment_method') }}</option>
                                <option value="cash">{{ __('labels.cash') }}</option>
                                <option value="cheque">{{ __('labels.cheque') }}</option>
                                <option value="bank_transfer">{{ __('labels.bank_transfer') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.paid_at') }} <span class="text-danger">*</span></label>
                            <input type="date" name="paid_at" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="col-md-4 payment-fields payment-fields-cash d-none">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.receipt_number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="receipt_number" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-4 payment-fields payment-fields-cash d-none">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.received_by') }} <span class="text-danger">*</span></label>
                            <input type="text" name="received_by" class="form-control" maxlength="100">
                        </div>
                    </div>

                    <div class="col-md-4 payment-fields payment-fields-cheque d-none">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.cheque_number') }} <span class="text-danger">*</span></label>
                            <input type="text" name="cheque_number" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-4 payment-fields payment-fields-cheque d-none">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.bank_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-4 payment-fields payment-fields-cheque d-none">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.cheque_date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="cheque_date" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-4 payment-fields payment-fields-bank_transfer d-none">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.utr_no') }} <span class="text-danger">*</span></label>
                            <input type="text" name="utr_no" class="form-control" maxlength="100">
                        </div>
                    </div>
                    <div class="col-md-4 payment-fields payment-fields-bank_transfer d-none">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.bank_name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="bank_name" class="form-control" maxlength="100">
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group mb-2">
                            <label class="form-label">{{ __('labels.remarks') }}</label>
                            <textarea name="remarks" class="form-control" rows="2" maxlength="1000"></textarea>
                        </div>
                    </div>

                    <div class="col-md-12 text-end">
                        <button type="button" class="btn btn-sm btn-secondary" id="cancelPaymentForm">{{ __('labels.cancel') }}</button>
                        <button type="submit" class="btn btn-sm btn-success">{{ __('labels.add_payment') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
    @endcan

    <div class="table-responsive">
        <table class="table table-sm table-striped table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>{{ __('labels.#') }}</th>
                    <th>{{ __('labels.payment_id') }}</th>
                    <th>{{ __('labels.payment_method') }}</th>
                    <th>{{ __('labels.payment_details') }}</th>
                    <th>{{ __('labels.payment_status') }}</th>
                    <th class="text-end">{{ __('labels.amount') }}</th>
                    <th>{{ __('labels.paid_at') }}</th>
                    <th>{{ __('labels.remarks') }}</th>
                    @can('bookings edit')
                    <th class="text-center">{{ __('labels.actions') }}</th>
                    @endcan
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
                    <td>{{ humanize($payment->payment_method) }}</td>
                    <td class="small">{!! formatPaymentDetails($payment->payment_details, $payment->payment_method) !!}</td>
                    <td>
                        <span class="badge {{ $itemPaymentBadgeClass }}">
                            {{ humanize($payment->payment_status) }}
                        </span>
                    </td>
                    <td class="text-end">{{ formatCurrency($payment->amount) }}</td>
                    <td>{{ $payment->paid_at ? formatDatetime($payment->paid_at) : '—' }}</td>
                    <td>{{ $payment->remarks ?? '—' }}</td>
                    @can('bookings edit')
                    <td class="text-center">
                        @if (in_array($payment->payment_method, \App\Services\BookingPaymentService::MANUAL_METHODS))
                        <a href="javascript:void(0);"
                           class="link-reset fs-20 p-1 delete-payment-btn"
                           data-url="{{ route('bookings.payments.destroy', [$booking->id, $payment->id]) }}"
                           title="{{ __('labels.delete') }}">
                            <i class="ti ti-trash text-danger"></i>
                        </a>
                        @else
                        —
                        @endif
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $paymentsTableCols }}" class="text-center text-muted">{{ __('labels.no_records') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    const bookingShowUrl = '{{ url(route('bookings.show', $booking->id)) }}';
    const bookingShowTitle = '{{ __('labels.booking_details') }} {{ formatBookingId($booking->id) }}';

    $('#togglePaymentForm').on('click', function() {
        $('#paymentFormWrapper').toggleClass('d-none');
    });

    $('#cancelPaymentForm').on('click', function() {
        $('#paymentFormWrapper').addClass('d-none');
    });

    function togglePaymentMethodFields(method) {
        $('.payment-fields').addClass('d-none');
        $('.payment-fields input, .payment-fields textarea').prop('required', false).prop('disabled', true);

        if (!method) {
            return;
        }

        const $fields = $('.payment-fields-' + method);
        $fields.removeClass('d-none');
        $fields.find('input, textarea').prop('required', true).prop('disabled', false);
    }

    $('#paymentMethod').on('change', function() {
        togglePaymentMethodFields($(this).val());
    });

    const reloadBookingModal = function() {
        largeModal(bookingShowUrl, bookingShowTitle);
    };

    initValidate('#addPaymentForm');

    $('#addPaymentForm').on('submit', function(e) {
        const form = $(this);
        ajaxSubmit(e, form, reloadBookingModal);
    });

    $('.delete-payment-btn').on('click', function() {
        if (!confirm('Are you sure you want to delete this payment?')) {
            return;
        }

        const url = $(this).data('url');
        const $btn = $(this);
        $btn.css('pointer-events', 'none');

        $.ajax({
            type: 'POST',
            url: url,
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'DELETE',
            },
            dataType: 'json',
            success: function(response) {
                $btn.css('pointer-events', 'inherit');

                if (response.status) {
                    Command: toastr['success'](response.notification, 'Success');
                    reloadBookingModal();
                } else {
                    Command: toastr['error'](response.notification, 'Alert');
                }
            },
            error: function() {
                $btn.css('pointer-events', 'inherit');
                Command: toastr['error']('An unexpected error occurred. Please try again later.', 'Error');
            },
        });
    });
});
</script>
