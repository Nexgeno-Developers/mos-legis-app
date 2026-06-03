@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{ __('labels.seats') }}</h4>
    </div>
</div>
@include('backend.includes.alert-message')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed align-items-center">
                <div class="row">
                    <div class="col-md-9">
                        <form id="seat-filter-form" class="row g-3 align-items-center">
                            <div class="col-sm-3">
                                <select name="property_id" id="filter_property_id" class="form-select select2-filter">
                                    <option value="">{{ __('labels.all_properties') }}</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}" {{ request()->get('property_id') == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select name="cabin_id" id="filter_cabin_id" class="form-select select2-filter">
                                    <option value="">{{ __('labels.all_cabins') }}</option>
                                    @foreach ($cabins as $cabin)
                                        <option value="{{ $cabin->id }}" data-property-id="{{ $cabin->property_id }}" {{ request()->get('cabin_id') == $cabin->id ? 'selected' : '' }}>
                                            {{ $cabin->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" name="search" class="form-control" value="{{ request()->get('search') }}" placeholder="{{ __('labels.search') }}">
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
                    <div class="col-md-3 text-end">
                        @can('seats create')
                        <button onclick="smallModal('{{ url(route($module . '.create', request()->only(['property_id', 'cabin_id']))) }}', '{{ __('labels.create') }}')"
                            class="btn btn-primary btn-icon w-100"><i class="ti ti-plus"></i> {{ __('labels.create') }}</button>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>{{ __('labels.#') }}</th>
                                <th>{{ __('labels.seat_no') }}</th>
                                <th>{{ __('labels.property') }}</th>
                                <th>{{ __('labels.cabin') }}</th>
                                <th>{{ __('labels.pricing') }}</th>
                                <th>{{ __('labels.status') }}</th>
                                <th>{{ __('labels.created') }}</th>
                                <th>{{ __('labels.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pageData as $index => $row)
                            <tr>
                                <td>{{ $pageData->firstItem() + $index }}</td>
                                <td>{{ $row->seat_no }}</td>
                                <td>{{ $row->property?->name }}</td>
                                <td>{{ $row->cabin?->name }}</td>
                                <td>
                                    <small>
                                        {{ __('labels.daily') }}: {{ $row->priceFor('daily') ?? '—' }}<br>
                                        {{ __('labels.monthly') }}: {{ $row->priceFor('monthly') ?? '—' }}<br>
                                        {{ __('labels.yearly') }}: {{ $row->priceFor('yearly') ?? '—' }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $row->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $row->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ formatDatetime($row->created_at) }}</td>
                                <td>
                                    @can('seats edit')
                                    <a href="javascript:void(0);" onclick="smallModal('{{ url(route($module . '.edit', $row->id)) }}', '{{ __('labels.update') }}')" class="link-reset fs-20 p-1"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('seats delete')
                                    <a href="javascript:void(0);" onclick="confirmModal('{{ route($module . '.destroy', $row->id) }}', callback)" class="link-reset fs-20 p-1"><i class="ti ti-trash"></i></a>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">{{ __('labels.no_records') }}</td>
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

@include('backend.seats.partials.cabin-filter-script')

<script defer>
$(document).ready(function() {
    initSelect2('.select2-filter');
    const allCabins = @json($allCabins);
    initSeatCabinFilter('#filter_property_id', '#filter_cabin_id', allCabins, '{{ request()->get('property_id') }}', '{{ request()->get('cabin_id') }}');
});

const callback = function(response) {
    setTimeout(function() {
        location.reload();
    }, 1500);
}
</script>
@endsection
