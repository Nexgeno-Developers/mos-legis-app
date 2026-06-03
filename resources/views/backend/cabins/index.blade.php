@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{ __('labels.cabins') }}</h4>
    </div>
</div>
@include('backend.includes.alert-message')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed align-items-center">
                <div class="row">
                    <div class="col-md-7">
                        <form class="row g-3 align-items-center">
                            <div class="col-sm-4">
                                <select name="property_id" class="form-select select2-filter">
                                    <option value="">{{ __('labels.all_properties') }}</option>
                                    @foreach ($properties as $property)
                                        <option value="{{ $property->id }}" {{ request()->get('property_id') == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-4">
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
                    <div class="col-md-2 offset-md-3 text-end">
                        @can('cabins create')
                        <button onclick="smallModal('{{ url(route($module . '.create')) }}', '{{ __('labels.create') }}')"
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
                                <th>{{ __('labels.thumbnail') }}</th>
                                <th>{{ __('labels.name') }}</th>
                                <th>{{ __('labels.property') }}</th>
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
                                <td>
                                    @if($row->thumbnail)
                                    <img src="{{ uploaded_asset($row->thumbnail, 'small') }}" alt="{{ $row->name }}" class="rounded" width="48" height="48" style="object-fit: cover;">
                                    @endif
                                </td>
                                <td>{{ $row->name }}</td>
                                <td>{{ $row->property?->name }}</td>
                                <td>
                                    <small>
                                        {{ __('labels.monthly') }}: {{ $row->formattedPriceSum($row->monthly_total) }}<br>
                                        {{ __('labels.yearly') }}: {{ $row->formattedPriceSum($row->yearly_total) }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge {{ $row->status ? 'bg-success' : 'bg-danger' }}">
                                        {{ $row->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ formatDatetime($row->created_at) }}</td>
                                <td>
                                    @can('seats view')
                                    <a href="{{ route('seats.index', ['property_id' => $row->property_id, 'cabin_id' => $row->id]) }}" class="link-reset fs-20 p-1" title="{{ __('labels.seats') }}">
                                        <i class="ti ti-armchair"></i>
                                    </a>
                                    @endcan
                                    @can('cabins edit')
                                    <a href="javascript:void(0);" onclick="smallModal('{{ url(route($module . '.edit', $row->id)) }}', '{{ __('labels.update') }}')" class="link-reset fs-20 p-1"><i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('cabins delete')
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

<script defer>
$(document).ready(function() {
    initSelect2('.select2-filter');
});

const callback = function(response) {
    setTimeout(function() {
        location.reload();
    }, 1500);
}
</script>
@endsection
