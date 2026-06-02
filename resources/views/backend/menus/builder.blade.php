@extends('backend.layouts.app')

@section('content')
<div class="page-title-head d-flex align-items-center gap-2">
    <div class="flex-grow-1">
        <h4 class="fs-16 text-uppercase fw-bold mb-0">{{ $moduleName ?? 'Menus' }}</h4>
    </div>
</div>
@include('backend.includes.alert-message')

<div class="row g-3">
    <!-- Menu Groups -->
    <div class="col-lg-4">
        <div class="card">
            {{-- <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fs-14 fw-semibold">Menu Groups</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="showAddGroupModal()" title="Create New Group">
                        <i class="ti ti-plus me-1"></i>
                        <span>New Group</span>
                    </button>
                </div>
            </div> --}}
            <div class="card-body">
                <label class="form-label small text-muted mb-1">Current group</label>
                <select class="form-select form-select-sm mb-3" id="menuGroupSelect" onchange="changeMenuGroup()">
                    @foreach($menuGroups as $group)
                    <option value="{{ $group->id }}" {{ $selectedMenuGroup == $group->id ? 'selected' : '' }}>
                        {{ $group->name }}
                    </option>
                    @endforeach
                </select>
                <label class="form-label small text-muted mb-1">All groups</label>
                <ul class="list-group list-group-flush">
                    @foreach($menuGroups as $group)
                    <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-2">
                        <span class="text-truncate">{{ $group->name }}</span>
                        <div class="d-flex gap-1 flex-shrink-0">
                            <button type="button" class="btn btn-sm btn-primary" onclick="showEditGroupModal({{ $group->id }})" title="Edit">
                                <i class="ti ti-pencil"></i>
                            </button>
                            {{-- <button type="button" class="btn btn-sm btn-danger" onclick="deleteGroup({{ $group->id }})" title="Delete">
                                <i class="ti ti-trash"></i>
                            </button> --}}
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <!-- Menu Items -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0 fs-14 fw-semibold">Menu Items</h5>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-light btn-sm" onclick="saveMenuOrder()" title="Save order">
                        <i class="ti ti-grip-vertical me-1"></i>
                        <span>Save Order</span>
                    </button>
                    <button type="button" class="btn btn-success btn-sm" onclick="showAddItemModal()" title="Add item">
                        <i class="ti ti-plus me-1"></i>
                        <span>Add Item</span>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="menuTree" class="dd menu-tree">
                    <ol class="dd-list" id="menuItemsList">
                        @include('backend.menus.partials.menu-item', ['menuItems' => $menuItems])
                    </ol>
                </div>
                @if($menuItems->isEmpty())
                <p class="text-muted small text-center py-4 mb-0">
                    <i class="ti ti-menu-2 d-block fs-24 mb-2 opacity-50"></i>
                    No menu items yet. Click "Add Item" to create one.
                </p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Menu Group Modal -->
<div class="modal fade" id="menuGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fs-14 fw-semibold" id="menuGroupModalTitle">Add Menu Group</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="menuGroupForm">
                @csrf
                <input type="hidden" id="groupId">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="groupName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="groupSlug" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="groupDescription" rows="2"></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="groupStatus">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add/Edit Menu Item Modal -->
<div class="modal fade" id="menuItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fs-14 fw-semibold" id="menuItemModalTitle">Add Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="menuItemForm">
                @csrf
                <input type="hidden" id="itemId">
                <input type="hidden" id="itemMenuGroupId" value="{{ $selectedMenuGroup }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="itemName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="itemUrl" required placeholder="https://example.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon (optional)</label>
                        <input type="text" class="form-control" id="itemIcon" placeholder="ti ti-home">
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select class="form-select" id="itemStatus">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- CSS for Nestable (aligned with admin panel) -->
<style>
    .menu-tree.dd {
        position: relative;
        display: block;
        margin: 0;
        padding: 0;
        max-width: 100%;
        list-style: none;
        font-size: 13px;
        line-height: 20px;
    }

    .menu-tree .dd-list {
        display: block;
        position: relative;
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .menu-tree .dd-list .dd-list {
        padding-left: 1.5rem;
    }

    .menu-tree .dd-collapsed .dd-list {
        display: none;
    }

    .menu-tree .dd-item,
    .menu-tree .dd-empty,
    .menu-tree .dd-placeholder {
        display: block;
        position: relative;
        margin: 0;
        padding: 0;
        min-height: 20px;
        font-size: 13px;
        line-height: 20px;
    }

    .menu-tree .dd-item {
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .menu-tree .dd-item > .dd-list {
        flex: 0 0 100%;
        width: 100%;
        margin-top: 0.25rem;
    }

    .menu-tree .dd-handle {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: center;
        height: 40px;
        margin: 0;
        padding: 0 0.75rem 0 2.2rem;
        color: var(--ct-body-color, #495057);
        text-decoration: none;
        font-weight: 500;
        border: 1px solid var(--ct-border-color, #e9ecef);
        background: var(--ct-card-bg, #fff);
        border-radius: 0.25rem;
        box-sizing: border-box;
        cursor: move;
        transition: border-color 0.15s ease, background-color 0.15s ease;
    }

    .menu-tree .dd-handle:hover {
        border-color: var(--ct-primary, #405189);
        background: var(--ct-card-bg, #fff);
    }

    .menu-tree .dd-item > button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        left: 0.40rem;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        width: 26px;
        height: 26px;
        margin: 0;
        padding: 0;
        border-radius: 0.25rem;
        border: 1px solid var(--ct-border-color, #e9ecef);
        background-color: #f8f9fa;
        font-size: 0; /* hide original 'Expand/Collapse' text */
        line-height: 1;
        color: var(--ct-secondary, #495057);
        overflow: hidden;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08);
    }

    .menu-tree .dd-item > button:before {
        content: '+';
        display: block;
        font-size: 12px;
        line-height: 1;
        text-align: center;
        font-weight: 600;
    }

    .menu-tree .dd-item > button[data-action="collapse"]:before {
        content: '−';
    }

    .menu-tree .dd-item > button.dd-expand { display: none; }
    .menu-tree .dd-item.dd-collapsed > button.dd-expand { display: inline-flex; }
    .menu-tree .dd-item.dd-collapsed > button.dd-collapse { display: none; }

    /* Fine-tune vertical alignment of collapse button when expanded */
    .menu-tree .dd-item > button.dd-collapse {
        top: 50%;
        transform: translateY(-50%);
    }

    button.dd-collapse {
        top: 20px !important;
    }   

    .menu-tree .dd-placeholder,
    .menu-tree .dd-empty {
        margin: 0.25rem 0;
        padding: 0;
        min-height: 38px;
        background: rgba(var(--ct-primary-rgb, 64, 81, 137), 0.06);
        border: 1px dashed var(--ct-border-color, #e9ecef);
        border-radius: 0.25rem;
        box-sizing: border-box;
    }

    .menu-tree .dd-dragel {
        position: absolute;
        pointer-events: none;
        z-index: 9999;
    }

    .menu-tree .dd-dragel .dd-handle {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
    }

    .menu-tree .menu-item-actions {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        flex-shrink: 0;
    }

    .menu-tree .menu-item-actions .btn {
        padding: 0.375rem 0.6rem;
        font-size: 12px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
</style>

<!-- Nestable JS Library -->
<script src="https://cdn.jsdelivr.net/npm/nestable2@1.6.0/jquery.nestable.min.js"></script>

<script>
    let nestable;

    $(document).ready(function() {
        initializeNestable();
        setupFormSubmissions();
    });

    function initializeNestable() {
        nestable = $('#menuTree').nestable({
            group: 1,
            maxDepth: 10
        });
    }

    function setupFormSubmissions() {
        // Menu Group Form
        $('#menuGroupForm').submit(function(e) {
            e.preventDefault();
            saveMenuGroup();
        });

        // Menu Item Form
        $('#menuItemForm').submit(function(e) {
            e.preventDefault();
            saveMenuItem();
        });
    }

    function changeMenuGroup() {
        const menuGroupId = $('#menuGroupSelect').val();
        window.location.href = '{{ route("backend.menus") }}?group=' + menuGroupId;
    }

    function showAddGroupModal() {
        $('#menuGroupModalTitle').text('Add Menu Group');
        $('#groupId').val('');
        $('#groupName').val('');
        $('#groupSlug').val('');
        $('#groupDescription').val('');
        $('#groupStatus').val(1);
        $('#menuGroupModal').modal('show');
    }

    function showEditGroupModal(groupId) {
        // Fetch group data and populate modal
        $.ajax({
            url: '{{ route("backend.menus.group.save") }}',
            method: 'POST',
            data: {
                id: groupId
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    const group = response.menu_group;
                    $('#menuGroupModalTitle').text('Edit Menu Group');
                    $('#groupId').val(group.id);
                    $('#groupName').val(group.name);
                    $('#groupSlug').val(group.slug);
                    $('#groupDescription').val(group.description);
                    $('#groupStatus').val(group.status ? 1 : 0);
                    $('#menuGroupModal').modal('show');
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function(field, messages) {
                        toastr.error(messages[0]);
                    });
                } else {
                    toastr.error('Failed to fetch menu group');
                }
            }
        });
    }

    function saveMenuGroup() {
        const data = {
            id: $('#groupId').val(),
            name: $('#groupName').val(),
            slug: $('#groupSlug').val(),
            description: $('#groupDescription').val(),
            status: $('#groupStatus').val()
        };

        $.ajax({
            url: '{{ route("backend.menus.group.save") }}',
            method: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#menuGroupModal').modal('hide');
                    toastr.success('Menu group saved successfully');
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function(field, messages) {
                        toastr.error(messages[0]);
                    });
                }
            }
        });
    }

    function showAddItemModal() {
        $('#menuItemModalTitle').text('Add Menu Item');
        $('#itemId').val('');
        $('#itemName').val('');
        $('#itemUrl').val('');
        $('#itemIcon').val('');
        $('#itemStatus').val(1);
        $('#itemMenuGroupId').val($('#menuGroupSelect').val());
        $('#menuItemModal').modal('show');
    }

    function showEditItemModal(itemId) {
        $.ajax({
            url: '{{ route("backend.menus.item.save") }}',
            method: 'POST',
            data: {
                id: itemId
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    const item = response.menu_item;
                    $('#menuItemModalTitle').text('Edit Menu Item');
                    $('#itemId').val(item.id);
                    $('#itemName').val(item.name);
                    $('#itemUrl').val(item.url);
                    $('#itemIcon').val(item.icon);
                    $('#itemStatus').val(item.status ? 1 : 0);
                    $('#itemMenuGroupId').val(item.menu_group_id);
                    $('#menuItemModal').modal('show');
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function(field, messages) {
                        toastr.error(messages[0]);
                    });
                } else {
                    toastr.error('Failed to fetch menu item');
                }
            }
        });
    }

    function saveMenuItem() {
        const data = {
            id: $('#itemId').val(),
            menu_group_id: $('#itemMenuGroupId').val(),
            name: $('#itemName').val(),
            url: $('#itemUrl').val(),
            icon: $('#itemIcon').val(),
            status: $('#itemStatus').val()
        };

        $.ajax({
            url: '{{ route("backend.menus.item.save") }}',
            method: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#menuItemModal').modal('hide');
                    toastr.success('Menu item saved successfully');
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function(field, messages) {
                        toastr.error(messages[0]);
                    });
                }
            }
        });
    }

    function deleteGroup(groupId) {
        if (confirm('Are you sure you want to delete this menu group? This will also delete all associated menu items.')) {
            $.ajax({
                url: '{{ route("backend.menus.group.delete") }}',
                method: 'POST',
                data: {
                    id: groupId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Menu group deleted successfully');
                        setTimeout(function() {
                            window.location.reload();
                        }, 500);
                    }
                },
                error: function(xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        $.each(errors, function(field, messages) {
                            toastr.error(messages[0]);
                        });
                    } else {
                        toastr.error('Failed to delete menu group');
                    }
                }
            });
        }
    }

    function deleteItem(itemId) {
        if (confirm('Are you sure you want to delete this menu item?')) {
            $.ajax({
                url: '{{ route("backend.menus.item.delete") }}',
                method: 'POST',
                data: {
                    id: itemId
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Menu item deleted successfully');
                        setTimeout(function() {
                            window.location.reload();
                        }, 500);
                    }
                }
            });
        }
    }

    function saveMenuOrder() {
        const menuGroupId = $('#menuGroupSelect').val();
        const items = $('#menuTree').nestable('serialize');

        $.ajax({
            url: '{{ route("backend.menus.save-order") }}',
            method: 'POST',
            data: {
                menu_group_id: menuGroupId,
                items: items
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                if (errors) {
                    $.each(errors, function(field, messages) {
                        toastr.error(messages[0]);
                    });
                }
            }
        });
    }
</script>
@endsection
