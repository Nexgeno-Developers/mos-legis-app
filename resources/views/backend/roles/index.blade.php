@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{$module}}</h4>
    </div>
</div>
@include('backend.includes.alert-message')

<div class="card">
    <div class="card-header border-bottom border-dashed align-items-center">
        <div class="row">
            <div class="col-md-2 offset-md-10 text-end">
            @can('roles create')
                <button onclick="smallModal('{{url(route($module . '.create'))}}', '{{__('labels.create')}}')"
                class="btn btn-primary btn-icon w-100"><i class="ti ti-plus"></i> {{__("labels.create")}}</button>
            @endcan
            </div>
        </div>
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>{{__("labels.#")}}</th>
                    <th>{{__("labels.name")}}</th>
                    <th>{{__("labels.created")}}</th>
                    <th>{{__("labels.actions")}}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pageData as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ ucfirst($row->name) }}</td>
                    <td>{{ formatDatetime($row->created_at) }}</td>
                    <td>
                        @can('roles edit')
                        <a href="javascript:void(0);" onclick="smallModal('{{ url(route($module . '.edit', $row->id)) }}', '{{__('labels.update')}}')" class="link-reset fs-20 p-1">
                            <i class="ti ti-pencil"></i>
                        </a>
                        @endcan
                        @if($row->id != 1)
                            @can('roles delete')
                            <a href="javascript:void(0);" onclick="confirmModal('{{ route($module . '.destroy', $row->id) }}')" class="link-reset fs-20 p-1">
                                <i class="ti ti-trash"></i>
                            </a>
                            @endcan
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $pageData->links() }}
    </div>
</div>
@endsection