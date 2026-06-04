@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{$moduleName}}</h4>
    </div>
    @can('companies create')
    <!-- <div class="text-end">
        <a href="{{ route('companies.create') }}" class="btn btn-primary btn-icon">
            <i class="ti ti-plus"></i> Create
        </a>
    </div> -->
    @endcan
</div>

<div class="row">
    <div class="col-xl-12">
        <div class="card">
        <div class="card-header border-bottom border-dashed align-items-center">
            <div class="row w-100">
                <div class="col-md-12">
                    <!-- Company filter removed -->
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Website</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pageData as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $row->name }}</td>
                                <td><a target="_blank" href="{{ $row->website }}">{{ $row->website }}</a></td>
                                <td>{{ $row->email }}</td>
                                <td>{{ $row->phone }}</td>
                                <td>
                                    @can('companies edit')
                                    <a href="{{ route('companies.edit', $row->id) }}" class="link-reset fs-20 p-1"> <i class="ti ti-pencil"></i></a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div> <!-- end table-responsive-->
            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
</div><!-- end row-->
@endsection