@foreach($menuItems as $menuItem)
<li class="dd-item" data-id="{{ $menuItem->id }}">
    <div class="dd-handle">
        {{ $menuItem->name }}
        @if($menuItem->icon)
            <span class="text-muted small ms-1">({{ $menuItem->icon }})</span>
        @endif
    </div>
    <div class="menu-item-actions">
        <button type="button" class="btn btn-sm btn-primary btn-icon" onclick="showEditItemModal({{ $menuItem->id }})" title="Edit">
            <i class="ti ti-pencil"></i>
        </button>
        <button type="button" class="btn btn-sm btn-danger btn-icon" onclick="deleteItem({{ $menuItem->id }})" title="Delete">
            <i class="ti ti-trash"></i>
        </button>
    </div>
    @if($menuItem->children->count() > 0)
    <ol class="dd-list">
        @include('backend.menus.partials.menu-item', ['menuItems' => $menuItem->children])
    </ol>
    @endif
</li>
@endforeach