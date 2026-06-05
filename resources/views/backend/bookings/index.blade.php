@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{ __('labels.bookings') }}</h4>
    </div>
</div>
@include('backend.includes.alert-message')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed align-items-center">
                <div class="row g-3 align-items-center">
                    <div class="col-md-10">
                        <form class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" value="{{ request()->get('search') }}" placeholder="{{ __('labels.search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="booking_status" class="form-select select2">
                                    <option value="">{{ __('labels.all_booking_statuses') }}</option>
                                    @foreach (['reserved', 'active', 'completed', 'cancelled'] as $status)
                                    <option value="{{ $status }}" {{ request()->get('booking_status') === $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="payment_status" class="form-select select2">
                                    <option value="">{{ __('labels.all_payment_statuses') }}</option>
                                    @foreach (['paid', 'unpaid', 'partially_paid'] as $status)
                                    <option value="{{ $status }}" {{ request()->get('payment_status') === $status ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success btn-icon">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                            <div class="col-auto">
                                <button type="reset" class="btn btn-warning btn-icon"
                                    onclick="window.location.href = '{{ route(Route::currentRouteName()) }}';">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('labels.booking_id') }}</th>
                                <th>{{ __('labels.invoice_number') }}</th>
                                <th>{{ __('labels.name') }}</th>
                                <th>{{ __('labels.cabins_and_seats') }}</th>
                                <th>{{ __('labels.duration') }}</th>
                                <th>{{ __('labels.amount') }}</th>
                                <th>{{ __('labels.booking_status') }}</th>
                                <th>{{ __('labels.payment_status') }}</th>
                                <th>{{ __('labels.created') }}</th>
                                <th>{{ __('labels.options') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pageData as $row)
                            <tr>
                                <td>{{ $row->id }}</td>
                                <td>{{ $row->invoice_no }}</td>
                                <td>{{ $row->user?->name ?? '—' }}</td>
                                <td>
                                    <em>{{ $row->property?->name ?? '—' }}</em> 
                                    @forelse ($row->cabinsAndSeatsLines() as $line)
                                    <div>{{ $line }}</div>
                                    @empty
                                    <span class="text-muted">—</span>
                                    @endforelse
                                </td>
                                <td>
                                    {{ formatDate($row->start_datetime) }} – {{ formatDate($row->end_datetime) }}
                                </td>
                                <td>{{ formatCurrency($row->grand_total_amount) }}</td>
                                <td>
                                    @php
                                        $bookingBadgeClass = match ($row->booking_status) {
                                            'active' => 'bg-success',
                                            'completed' => 'bg-info',
                                            'cancelled' => 'bg-danger',
                                            default => 'bg-warning',
                                        };
                                    @endphp
                                    <span class="badge {{ $bookingBadgeClass }}">
                                        {{ ucfirst($row->booking_status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $paymentBadgeClass = match ($row->payment_status) {
                                            'paid' => 'bg-success',
                                            'partially_paid' => 'bg-warning',
                                            default => 'bg-danger',
                                        };
                                    @endphp
                                    <span class="badge {{ $paymentBadgeClass }}">
                                        {{ ucfirst(str_replace('_', ' ', $row->payment_status)) }}
                                    </span>
                                </td>
                                <td>{{ formatDatetime($row->created_at) }}</td>
                                <td>
                                    @can('bookings delete')
                                    <!-- <a href="javascript:void(0);" onclick="confirmModal('{{ route($module . '.destroy', $row->id) }}', callback)" class="link-reset fs-20 p-1" title="{{ __('labels.delete') }}"><i class="ti ti-trash"></i></a> -->
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">{{ __('labels.no_records') }}</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $pageData->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script defer>
$(document).ready(function() {
    initSelect2();
});

const callback = function(response) {
    setTimeout(function() {
        location.reload();
    }, 1500);
}
</script>
@endsection
