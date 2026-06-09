@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{ __('labels.seat_availability') }}</h4>
    </div>
    <div>
        <a href="{{ route('bookings.index') }}" class="btn btn-link btn-sm">
            <i class="ti ti-calendar-event me-1"></i> {{ __('labels.bookings') }}
        </a>
    </div>
</div>
@include('backend.includes.alert-message')

@forelse ($properties as $property)
@php
    $propertySeats = $property->cabins->flatMap->seats;
    $activeCount = 0;
    $reservedCount = 0;
    $availableCount = 0;

    foreach ($propertySeats as $seat) {
        $seatInfo = $seatStatusMap[$seat->id] ?? null;
        $seatStatus = $seatInfo['status'] ?? 'available';

        match ($seatStatus) {
            'active' => $activeCount++,
            'reserved' => $reservedCount++,
            default => $availableCount++,
        };
    }
@endphp
<div class="card mb-4 seat-availability-property">
    <div class="card-header border-bottom border-dashed">
        <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
            <div>
                <h5 class="mb-1 d-flex align-items-center gap-2">
                    <i class="ti ti-building text-primary"></i>
                    {{ $property->name }}
                </h5>
                @if ($property->address)
                <p class="text-muted mb-0 small">{{ $property->address }}</p>
                @endif
            </div>
            <div class="seat-availability-legend d-flex flex-wrap gap-3">
                <span class="seat-legend-item">
                    <span class="seat-legend-dot seat-legend-active"></span>
                    {{ __('labels.active') }} <strong>{{ $activeCount }}</strong>
                </span>
                <span class="seat-legend-item">
                    <span class="seat-legend-dot seat-legend-reserved"></span>
                    {{ __('labels.reserved') }} <strong>{{ $reservedCount }}</strong>
                </span>
                <span class="seat-legend-item">
                    <span class="seat-legend-dot seat-legend-available"></span>
                    {{ __('labels.available') }} <strong>{{ $availableCount }}</strong>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body">
        @if ($property->cabins->isEmpty())
        <p class="text-muted mb-0">{{ __('labels.no_records') }}</p>
        @else
        <div class="row g-3">
            @foreach ($property->cabins as $cabin)
            <div class="col-xl-4 col-lg-6">
                <div class="card border h-100 mb-0 seat-availability-cabin">
                    <div class="card-header py-2 bg-light-subtle d-flex align-items-center justify-content-between">
                        <span class="fw-semibold d-flex align-items-center gap-2">
                            <i class="ti ti-door text-secondary"></i>
                            {{ $cabin->name }}
                        </span>
                        <span class="badge bg-secondary-subtle text-secondary">
                            {{ $cabin->seats->count() }} {{ __('labels.seats') }}
                        </span>
                    </div>
                    <div class="card-body">
                        @if ($cabin->seats->isEmpty())
                        <p class="text-muted small mb-0">{{ __('labels.no_records') }}</p>
                        @else
                        <div class="seat-grid">
                            @foreach ($cabin->seats as $seat)
                            @php
                                $seatInfo = $seatStatusMap[$seat->id] ?? null;
                                $seatStatus = $seatInfo['status'] ?? 'available';
                                $tooltipTitle = '';

                                if ($seatInfo) {
                                    $tooltipTitle = e($seatInfo['user_name'] ?? '—')
                                        .'<br>'
                                        .e(formatDate($seatInfo['start_datetime']))
                                        .' – '
                                        .e(formatDate($seatInfo['end_datetime']));
                                }
                            @endphp
                            <div
                                class="seat-item seat-{{ $seatStatus }}"
                                @if ($seatInfo)
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                data-bs-html="true"
                                title="{!! $tooltipTitle !!}"
                                @endif
                            >
                                <i class="ti ti-armchair"></i>
                                <span class="seat-no">{{ $seat->seat_no }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@empty
<div class="card">
    <div class="card-body text-center text-muted py-5">
        {{ __('labels.no_records') }}
    </div>
</div>
@endforelse

<script defer>
$(document).ready(function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
});
</script>
@endsection
