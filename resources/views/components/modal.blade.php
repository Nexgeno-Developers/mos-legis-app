@props(['name', 'title', 'subtitle' => null, 'open' => false, 'defaults' => [], 'maxWidth' => 'max-w-2xl'])
{{--
    Open with: $dispatch('open-modal', { name: '{{ $name }}', record: {...} }).
    The record is available inside the modal as `form`.
--}}
<div x-data="modal(@js($open), @js($defaults))" x-cloak
    x-on:open-modal.window="if ($event.detail.name === @js($name)) show($event.detail.record ?? {})"
    x-on:keydown.escape.window="hide()">
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-foreground/40 p-4 py-12">
        <div role="dialog" aria-modal="true" aria-label="{{ $title }}" @click.outside="hide()" class="w-full {{ $maxWidth }} border border-border bg-card shadow-xl">
            <div class="flex items-start justify-between gap-6 border-b border-border p-7">
                <div>
                    <h2 class="font-display text-2xl text-foreground">{{ $title }}</h2>
                    @if ($subtitle)<p class="measure mt-1 text-base text-muted-foreground">{{ $subtitle }}</p>@endif
                </div>
                <button type="button" @click="hide()" aria-label="Close dialog" class="text-2xl leading-none text-muted-foreground hover:text-foreground">&times;</button>
            </div>
            {{ $slot }}
        </div>
    </div>
</div>
