{{-- One menu item row in the admin menu builder. Expects $item (MenuItem). --}}
<div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-3 py-2.5">
    @can('menus.edit')
        <button type="button" data-handle class="flex h-8 w-6 shrink-0 cursor-grab items-center justify-center text-muted-foreground hover:text-foreground active:cursor-grabbing" aria-label="Drag to reorder {{ $item->label }}" title="Drag to reorder">
            <x-icon name="grip-vertical" class="h-4 w-4" />
        </button>
    @endcan
    <span @class(['flex h-8 w-8 shrink-0 items-center justify-center', 'bg-primary text-primary-foreground' => $item->isGroup(), 'bg-secondary text-primary' => ! $item->isGroup()])>
        <x-icon :name="$item->isGroup() ? 'folder' : 'link'" class="h-4 w-4" />
    </span>
    <div class="min-w-0 flex-1">
        <p class="flex flex-wrap items-center gap-2 font-medium">
            <span class="break-words">{{ $item->label }}</span>
            @unless ($item->isActive())<x-badge tone="muted">Hidden</x-badge>@endunless
            @if ($item->open_in_new_tab)<x-icon name="external-link" class="h-3.5 w-3.5 text-muted-foreground" title="Opens in a new tab" />@endif
        </p>
        <p class="truncate text-sm text-muted-foreground">
            {{ $item->link_type->shortLabel() }} · <span @if ($item->isGroup()) data-link-count @endif>{{ $item->destination() }}</span>
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-3">
        @if ($item->isGroup())
            @can('menus.create')
                <x-action-link icon="plus" @click="$dispatch('open-modal', { name: 'menu-item', record: { parent_id: '{{ $item->id }}' } })">Add link</x-action-link>
            @endcan
        @endif
        @can('menus.edit')
            <x-action-link icon="pencil" @click="$dispatch('open-modal', { name: 'menu-item', record: {{ Js::from([
                'label' => $item->label, 'link_type' => $item->link_type->value, 'route_name' => (string) $item->route_name,
                'page_id' => (string) $item->page_id, 'url' => (string) $item->url, 'parent_id' => (string) $item->parent_id,
                'open_in_new_tab' => $item->open_in_new_tab, 'status' => $item->status->value,
                'action' => route('admin.menus.items.update', $item), 'method' => 'PUT',
            ]) }} })">Edit</x-action-link>
            <form method="POST" action="{{ route('admin.menus.items.toggle-status', $item) }}">
                @csrf @method('PATCH')
                <x-action-link type="submit" :icon="$item->isActive() ? 'eye-off' : 'eye'">{{ $item->isActive() ? 'Hide' : 'Show' }}</x-action-link>
            </form>
        @endcan
        @can('menus.delete')
            <x-delete-button :action="route('admin.menus.items.destroy', $item)"
                :confirm="$item->isGroup() && $item->children->isNotEmpty() ? 'Delete this group and the '.$item->children->count().' link(s) inside it?' : 'Delete “'.$item->label.'” from the menu?'" />
        @endcan
    </div>
</div>
