@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{ $moduleName }}</h4>
    </div>
</div>
@include('backend.includes.alert-message')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed align-items-center">
                <div class="row g-3 align-items-end">
                    <div class="col-md-10">
                        <form class="row g-3 align-items-center">
                            <div class="col-md-2">
                                <input type="text" name="search" class="form-control" value="{{ $search }}"
                                    placeholder="Search user, module, action, remarks, IP, record ID">
                            </div>
                            <div class="col-md-2">
                                <select name="module" class="form-select select2-filter">
                                    <option value="">All Modules</option>
                                    @foreach ($modules as $moduleOption)
                                    <option value="{{ $moduleOption }}" {{ $module === $moduleOption ? 'selected' : '' }}>
                                        {{ ucfirst($moduleOption) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="action" class="form-select select2-filter">
                                    <option value="">All Actions</option>
                                    @foreach ($actions as $actionOption)
                                    <option value="{{ $actionOption }}" {{ $action === $actionOption ? 'selected' : '' }}>
                                        {{ ucfirst($actionOption) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_from" class="form-control" value="{{ $dateFrom }}" placeholder="From">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="date_to" class="form-control" value="{{ $dateTo }}" placeholder="To">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-success btn-icon w-100">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                            <div class="col-md-1">
                                <button type="reset" class="btn btn-warning btn-icon w-100"
                                    onclick="window.location.href = '{{ route('activity-logs.index') }}';">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    @can('activity-logs delete')
                    <div class="col-md-2 text-end">
                        <button title="Delete logs older than 30 days" type="button" class="btn btn-danger w-100" onclick="confirmModal('{{ route('activity-logs.clear-last-30-days') }}')">
                            <i class="ti ti-trash"></i> Clear Logs
                        </button>
                    </div>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Module</th>
                                <th>Action</th>
                                <th>Record ID</th>
                                <th>Remarks</th>
                                <th>IP Address</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($pageData as $index => $row)
                            <tr>
                                <td>{{ $pageData->firstItem() + $index }}</td>
                                <td>
                                    @if ($row->user)
                                    <div>{{ $row->user->name }}</div>
                                    <small class="text-muted">{{ $row->user->email }}</small>
                                    @else
                                    <span class="text-muted">System</span>
                                    @endif
                                </td>
                                <td><span class="">{{ $row->module }}</span></td>
                                <td>
                                    <span class="">{{ ucfirst($row->action) }}</span>
                                </td>
                                <td>{{ $row->record_id ?? '—' }}</td>
                                <td>{{ text_limit($row->remarks, 40) }}</td>
                                <td>{{ $row->ip_address ?? '—' }}</td>
                                <td>{{ formatDatetime($row->created_at) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No activity logs found.</td>
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
</script>
@endsection
