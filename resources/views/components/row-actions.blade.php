@props(['label' => 'Actions'])
{{--
    "⋯" menu for a table row's actions. Put the usual <x-action-link>, <x-delete-button> and small
    forms inside; each becomes one line of the menu. The menu is positioned on screen (fixed), so it is
    never clipped by a scrolling table, and it opens upwards near the bottom of the window.
--}}
@if (trim($slot) === '')
    <span class="text-muted-foreground">—</span>
@else
    <div x-data="rowActions" class="relative inline-block" @keydown.escape.window="close()" @scroll.window="close()" @resize.window="close()">
        <button type="button" x-ref="trigger" @click="toggle()" :aria-expanded="open" aria-haspopup="menu" aria-label="{{ $label }}" title="{{ $label }}"
            class="grid h-8 w-8 place-items-center border border-transparent text-muted-foreground transition-colors hover:border-border hover:bg-card hover:text-foreground"
            :class="open && 'border-border bg-card text-foreground'">
            <x-icon name="ellipsis-vertical" class="h-4 w-4" />
        </button>
        <div x-ref="menu" x-show="open" x-cloak @click.outside="close()" @click="$nextTick(() => close())" :style="style" role="menu"
            class="row-actions-menu fixed z-[60] min-w-44 border border-border bg-card py-1 text-left shadow-lg">
            {{ $slot }}
        </div>
    </div>
@endif
