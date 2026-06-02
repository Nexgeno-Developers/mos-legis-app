@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{$moduleName}}</h4>
    </div>
</div>
@include('backend.includes.alert-message')
<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed align-items-center">
                <div class="row">
                    <div class="col-md-10">
                        <form class="row g-3 align-items-center">
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" value="{{request()->get('search')}}" placeholder="Search by IP, URL, Referrer, Device, Browser, Platform">
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-success btn-icon w-100">
                                    <i class="ti ti-search"></i>
                                </button>
                            </div>
                            <div class="col-md-1">
                                <button type="reset" class="btn btn-warning btn-icon w-100" onclick="window.location.href = '{{ route(Route::currentRouteName()) }}';">
                                    <i class="ti ti-refresh"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row mt-3" id="bulkActionsContainer" style="display: none;">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" id="selectedCount">0 items selected</span>
                            <button type="button" class="btn btn-xs btn-danger" onclick="bulkDeleteVisitors()">
                                <i class="ti ti-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th width="50" class="text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()" style="cursor: pointer; width: 1.2em; height: 1.2em; margin-top: 0.25em;">
                                    </div>
                                </th>
                                <th>#</th>
                                <th>IP</th>
                                <th>URL</th>
                                <th>Referrer</th>
                                <th>Device</th>
                                <th>Browser</th>
                                <th>Platform</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pageData as $index => $row)
                            <tr>
                                <td class="text-center">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input row-checkbox" type="checkbox" value="{{ $row->id }}" onchange="updateBulkActions()" style="cursor: pointer; width: 1.2em; height: 1.2em; margin-top: 0.25em;">
                                    </div>
                                </td>
                                <td>{{ $pageData->firstItem() + $index }}</td>
                                <td>{{ $row->ip_address ?? 'N/A' }}</td>
                                <td>
                                    @if($row->url)
                                    <a href="{{ $row->url }}" target="_blank">
                                    {{ text_limit($row->url, 25) }}
                                    </a>
                                    @endif
                                </td>
                                <td>
                                    @if($row->referrer)
                                    <a href="{{ $row->referrer }}" target="_blank">
                                    {{ text_limit($row->referrer, 25) }}
                                    </a>
                                    @endif                                    
                                </td>
                                <td>{{ $row->device_type ?? 'N/A' }}</td>
                                <td>{{ $row->browser ?? 'N/A' }}</td>
                                <td>{{ $row->platform ?? 'N/A' }}</td>
                                <td>{{ formatDatetime($row->created_at) }}</td>
                                <td>
                                    <a href="javascript:void(0);" onclick="confirmModal('{{ route('visitors.destroy', $row->id) }}', callbackVisitors )" class="link-reset fs-20 p-1"> <i class="ti ti-trash"></i></a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $pageData->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script defer>
const callbackVisitors = function(response) {
    setTimeout(function() {
        location.reload();
    }, 1500);
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    updateBulkActions();
}

function updateBulkActions() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const selectedCount = checkboxes.length;
    const bulkActionsContainer = document.getElementById('bulkActionsContainer');
    const selectedCountSpan = document.getElementById('selectedCount');

    if (selectedCount > 0) {
        bulkActionsContainer.style.display = 'block';
        selectedCountSpan.textContent = selectedCount + ' item(s) selected';
    } else {
        bulkActionsContainer.style.display = 'none';
    }

    const allCheckboxes = document.querySelectorAll('.row-checkbox');
    const selectAll = document.getElementById('selectAll');
    if (allCheckboxes.length > 0) {
        selectAll.checked = selectedCount === allCheckboxes.length;
    }
}

function getSelectedIds() {
    const checkboxes = document.querySelectorAll('.row-checkbox:checked');
    const ids = Array.from(checkboxes).map(checkbox => checkbox.value);
    return ids;
}

function bulkDeleteVisitors() {
    const ids = getSelectedIds();
    if (ids.length === 0) {
        toastr.error('Please select at least one item');
        return;
    }

    const message = 'Are you sure you want to delete ' + ids.length + ' selected item(s)?';
    document.getElementById('bulk_delete_message').textContent = message;
    document.getElementById('bulk_delete_ids').value = ids.join(',');
    document.getElementById('bulk_delete_form').setAttribute('action', '{{ route("visitors.bulk-delete") }}');
    callBackFunction = callbackBulkVisitors;
    $('#bulkDeleteModal').modal('show');
}

const callbackBulkVisitors = function(response) {
    $('#bulkDeleteModal').modal('hide');

    if (response && response.status) {
        setTimeout(function() {
            location.reload();
        }, 1500);
    }
}
</script>
@endsection