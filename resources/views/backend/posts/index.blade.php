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
@endphp
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <form class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" value="{{request()->get('search')}}" placeholder="Search">
                            </div>
                            <div class="col-md-3">
                                <select name="layout" class="form-select select2">
                                    <option value="">All layouts</option>
                                    @foreach ($layouts as $layoutKey => $layoutData)
                                        <option value="{{ $layoutKey }}" @selected(request()->get('layout') === $layoutKey)>
                                            {{ $layoutData['label'] ?? ucfirst($layoutKey) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select select2">
                                    <option value="">All Status</option>
                                    <option value="1" @if(request()->get('status') == '1') selected @endif>Active</option>
                                    <option value="0" @if(request()->get('status') == '0') selected @endif>Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-success btn-icon w-100">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                            <div class="col-md-1">
                                <button type="reset" class="btn btn-warning btn-icon w-100"
                                    onclick="window.location.href = '{{ route(Route::currentRouteName()) }}';">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-3 d-flex justify-content-end">
                        <a href="{{ route($routeName . '.create') }}" class="btn btn-primary d-inline-flex align-items-center">
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
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Author</th>
                                <th>Layout</th>
                                <th>Status</th>
                                <th>Published At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($postData as $index => $row)
                            <tr>
                                <td>{{ $postData->firstItem() + $index }}</td>
                                <td>{{ $row->title }}</td>
                                <td>{{ $row->slug }}</td>
                                <td>{{ $row->author->name ?? 'N/A' }}</td>
                                <td>{{ $layouts[$row->layout]['label'] ?? ucfirst($row->layout) }}</td>
                                <td>
                                    <span class="badge {{ $row->is_active ? 'bg-success' : 'bg-danger' }}">
                                        {{ $row->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ $row->published_at ? formatDatetime($row->published_at) : '—' }}</td>
                                <td>{{ formatDatetime($row->updated_at) }}</td>
                                <td>
                                    <a href="{{ route($routeName . '.edit', $row->id) }}" class="link-reset fs-20 p-1">
                                        <i class="ti ti-pencil"></i>
                                    </a>
                                    <a href="javascript:void(0);" onclick="confirmModal('{{ route($routeName . '.destroy', $row->id) }}', callback )" class="link-reset fs-20 p-1">
                                        <i class="ti ti-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $postData->appends(request()->input())->links() }}
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
