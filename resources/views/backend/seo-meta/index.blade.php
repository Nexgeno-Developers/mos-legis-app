@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{ $module }}</h4>
    </div>
</div>
@include('backend.includes.alert-message')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed align-items-center">
                <div class="row">
                    <div class="col-md-5">
                        <form class="row g-3 align-items-center">
                            <div class="col-sm">
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
                    <div class="col-md-2 offset-md-5 text-end">
                        @can('seo-meta create')
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
                                <th>Slug</th>
                                <th>Meta Title</th>
                                <th>Robots</th>
                                <th>{{ __('labels.created') }}</th>
                                <th>{{ __('labels.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pageData as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->slug }}</td>
                                <td>{{ $row->meta_title ?: '-' }}</td>
                                <td>{{ $row->robots_index }}/{{ $row->robots_follow }}</td>
                                <td>{{ formatDatetime($row->created_at) }}</td>
                                <td>
                                    @can('seo-meta create')
                                    <a href="javascript:void(0);" onclick="smallModal('{{ url(route($module . '.clone', $row->id)) }}', '{{ __('labels.clone') }}')" class="link-reset fs-20 p-1" title="{{ __('labels.clone') }}"> <i class="ti ti-copy"></i></a>
                                    @endcan
                                    @can('seo-meta edit')
                                    <a href="javascript:void(0);" onclick="smallModal('{{ url(route($module . '.edit', $row->id)) }}', '{{ __('labels.update') }}')" class="link-reset fs-20 p-1"> <i class="ti ti-pencil"></i></a>
                                    @endcan
                                    @can('seo-meta delete')
                                    <a href="javascript:void(0);" onclick="confirmModal('{{ route($module . '.destroy', $row->id) }}', callback )" class="link-reset fs-20 p-1"> <i class="ti ti-trash"></i></a>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No records found.</td>
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
const callback = function(response) {
    setTimeout(function() {
        location.reload();
    }, 1500);
}
</script>
@endsection
