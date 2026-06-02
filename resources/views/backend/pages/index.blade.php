@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{$moduleName}}</h4>
    </div>
</div>
@include('backend.includes.alert-message')
@php
    $currentSort = request()->get('sort', 'id');
    $currentDirection = request()->get('direction', 'desc');
    $layouts = getPageLayouts();
@endphp
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <form class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" value="{{request()->get('search')}}" placeholder="Search">
                            </div>
                            <div class="col-md-4">
                                <select name="layout" class="form-select select2">
                                    <option value="">All layouts</option>
                                    @foreach ($layouts as $layoutKey => $layoutData)
                                        <option
                                            value="{{ $layoutKey }}"
                                            data-description="{{ $layoutData['description'] ?? '' }}"
                                            @selected(request()->get('layout') === $layoutKey)
                                        >
                                            {{ $layoutData['label'] ?? ucfirst($layoutKey) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-success btn-icon w-100">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                            <div class="col-md-2">
                                <button type="reset" class="btn btn-warning btn-icon w-100" 
                                    onclick="window.location.href = '{{ route(Route::currentRouteName()) }}';">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                            <input type="hidden" name="sort" value="{{ request()->get('sort', 'id') }}">
                            <input type="hidden" name="direction" value="{{ request()->get('direction', 'desc') }}">
                        </form>
                    </div>
                    <div class="col-md-4 d-flex justify-content-end">
                        <a href="{{ route($routeName . '.create') }}?layout=default"
                           class="btn btn-primary d-inline-flex align-items-center">
                            <i class="ti ti-plus"></i> Add New
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <!-- <th>Title</th> -->
                                <th>
                                @php
                                    // If currently sorting by title ascending, next click should set desc and vice versa
                                    $titleDirection = ($currentSort === 'title' && $currentDirection === 'asc') ? 'desc' : 'asc';
                                    // Build URL keeping existing query params but reset to page=1
                                    $titleUrl = request()->fullUrlWithQuery(['sort' => 'title', 'direction' => $titleDirection, 'page' => 1]);
                                @endphp

                                <a href="{{ $titleUrl }}" class="text-decoration-none">
                                    Title
                                    @if($currentSort === 'title')
                                    {{-- show active arrow --}}
                                    <span class="ms-1">{{ $currentDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @else
                                    {{-- subtle indicator when not active (optional) --}}
                                    <span class="ms-1 text-muted">⇅</span>
                                    @endif
                                </a>
                                </th>

                                <th>Slug</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pageData as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>                           
                                <td>{{ $row->title }}</td>                                                           
                                <td><a target="_blank" href="{{ $row->website }}">{{ $row->slug }}</a></td>                                                         
                                <td>{{ $layouts[$row->layout]['label'] ?? ucfirst($row->layout) }}</td>
                                <td>
                                <span class="badge {{ $row->is_active ? 'bg-success' : 'bg-danger' }}">
                                    {{ $row->is_active ? 'Active' : 'Inactive' }}
                                </span>                                    
                                </td>
                                <td>{{ formatDatetime($row->updated_at) }}</td>
                                <td>
                                    <a href="{{ route($routeName . '.edit', $row->id) }}?layout={{ $row->layout }}" class="link-reset fs-20 p-1">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                    <a href="javascript:void(0);" onclick="cloneConfirm('{{ route($routeName . '.clone', $row->id) }}')" class="link-reset fs-20 p-1" title="Clone">
                                        <i class="ti ti-copy"></i>
                                    </a>
                                    @if(!in_array($row->layout, ['home', 'thank_us'], true))
                                    <a href="javascript:void(0);" onclick="confirmModal('{{ route($routeName . '.destroy', $row->id) }}', callback )" class="link-reset fs-20 p-1"> <i class="ti ti-trash"></i></a>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $pageData->appends(request()->input())->links() }}
                </div> <!-- end table-responsive-->
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div><!-- end row-->

<script defer>
const callback = function(response) {
    setTimeout(function() {
        location.reload();
    }, 1500);
}
</script>
@endsection
