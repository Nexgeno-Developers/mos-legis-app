@extends('backend.layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">Dashboard</h4>
    </div>
</div>

<div class="row justify-content-center mt-1">
    @can('bookings view')
    @include('backend.includes.dashboard-stats-card', [
        'name' => 'Bookings',
        'icon' => 'ti ti-calendar-event',
        'stats' => [
            [
                'label' => 'Total Bookings',
                'icon' => 'ti ti-calendar-stats',
                'count' => $stats['bookings']['total'],
                'url' => route('bookings.index'),
            ],
            [
                'label' => 'Active',
                'icon' => 'ti ti-player-play',
                'count' => $stats['bookings']['active'],
                'url' => route('bookings.index', ['booking_status' => 'active']),
            ],
            [
                'label' => 'Reserved',
                'icon' => 'ti ti-bookmark',
                'count' => $stats['bookings']['reserved'],
                'url' => route('bookings.index', ['booking_status' => 'reserved']),
            ],
            [
                'label' => 'Completed',
                'icon' => 'ti ti-circle-check',
                'count' => $stats['bookings']['completed'],
                'url' => route('bookings.index', ['booking_status' => 'completed']),
            ],
            [
                'label' => 'Cancelled',
                'icon' => 'ti ti-calendar-off',
                'count' => $stats['bookings']['cancelled'],
                'url' => route('bookings.index', ['booking_status' => 'cancelled']),
            ],
        ],
    ])
    @endcan

    @can('bookings view')
    @include('backend.includes.dashboard-stats-card', [
        'name' => 'Payments',
        'icon' => 'ti ti-credit-card',
        'stats' => [
            [
                'label' => 'Total Paid Payments',
                'icon' => 'ti ti-cash',
                'count' => formatCurrency($stats['payments']['paid']),
            ],
            [
                'label' => 'Total Pending Payments',
                'icon' => 'ti ti-clock',
                'count' => formatCurrency($stats['payments']['pending']),
            ],
        ],
    ])
    @endcan

    @can('customers view')
    @include('backend.includes.dashboard-stats-card', [
        'name' => 'Customers',
        'icon' => 'ti ti-users',
        'stats' => [
            [
                'label' => 'Total Customers',
                'icon' => 'ti ti-users-group',
                'count' => $stats['customers']['total'],
                'url' => route('customers.index'),
            ],
            [
                'label' => 'Active',
                'icon' => 'ti ti-user-check',
                'count' => $stats['customers']['active'],
                'url' => route('customers.index'),
            ],
            [
                'label' => 'Inactive',
                'icon' => 'ti ti-user-off',
                'count' => $stats['customers']['inactive'],
                'url' => route('customers.index'),
            ],
        ],
    ])
    @endcan

    @canany(['properties view', 'cabins view', 'seats view'])
    @php
        $propertyStats = [];

        if (auth()->user()->can('properties view')) {
            $propertyStats[] = [
                'label' => 'Total Properties',
                'icon' => 'ti ti-building',
                'count' => $stats['properties']['total'],
                'url' => route('properties.index'),
            ];
        }

        if (auth()->user()->can('cabins view')) {
            $propertyStats[] = [
                'label' => 'Total Cabins',
                'icon' => 'ti ti-door',
                'count' => $stats['properties']['cabins'],
                'url' => route('cabins.index'),
            ];
        }

        if (auth()->user()->can('seats view')) {
            $propertyStats[] = [
                'label' => 'Total Seats',
                'icon' => 'ti ti-armchair',
                'count' => $stats['properties']['seats'],
                'url' => route('seats.index'),
            ];
        }
    @endphp
    @if (!empty($propertyStats))
    @include('backend.includes.dashboard-stats-card', [
        'name' => 'Properties',
        'icon' => 'ti ti-building-estate',
        'stats' => $propertyStats,
    ])
    @endif
    @endcanany
</div>
@endsection
