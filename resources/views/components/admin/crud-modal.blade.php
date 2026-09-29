@props([
    'name',
    'titleAdd',
    'titleEdit',
    'subtitle' => null,
    'storeUrl',
    'defaults' => [],
])
{{--
    Create/edit modal for small admin CRUD screens (wireframe crud-form-modal.tsx).
    Open for create:  $dispatch('open-modal', { name: '…' })
    Open for edit:    $dispatch('open-modal', { name: '…', record: { ...fields, action: updateUrl, method: 'PUT' } })
    Inputs bind with x-model="form.<field>". After a validation error the modal reopens with old input.
--}}
@php
    $reopen = $errors->any() && old('_modal') === $name;
    $initial = array_merge(
        ['action' => $storeUrl, 'method' => 'POST'],
        $defaults,
        $reopen ? array_merge(old(), ['action' => old('_modal_action', $storeUrl), 'method' => old('_method', 'POST')]) : [],
    );
@endphp
<div x-data="modal(@js($reopen), @js(array_merge(['action' => $storeUrl, 'method' => 'POST'], $defaults)))" x-cloak
    x-init="@if ($reopen) form = @js($initial) @endif"
    x-on:open-modal.window="if ($event.detail.name === @js($name)) show($event.detail.record ?? {})"
    x-on:keydown.escape.window="hide()">
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto bg-foreground/40 p-4 py-12">
        <div role="dialog" aria-modal="true" @click.outside="hide()" class="w-full max-w-2xl border border-border bg-card shadow-xl">
            <form method="POST" :action="form.action" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" :value="form.method">
                <input type="hidden" name="_modal" value="{{ $name }}">
                <input type="hidden" name="_modal_action" :value="form.action">

                <div class="flex items-start justify-between gap-6 border-b border-border p-7">
                    <div>
                        <h2 class="font-display text-2xl text-foreground" x-text="form.method === 'PUT' ? @js($titleEdit) : @js($titleAdd)"></h2>
                        @if ($subtitle)<p class="measure mt-1 text-base text-muted-foreground">{{ $subtitle }}</p>@endif
                    </div>
                    <button type="button" @click="hide()" aria-label="Close dialog" class="text-2xl leading-none text-muted-foreground hover:text-foreground">&times;</button>
                </div>

                <div class="space-y-5 p-7">
                    {{ $slot }}
                </div>

                <div class="flex flex-wrap justify-end gap-3 border-t border-border p-7">
                    <x-button type="button" variant="ghost" @click="hide()">Cancel</x-button>
                    <x-button type="submit" variant="primary" icon="save">Save</x-button>
                </div>
            </form>
        </div>
    </div>
</div>
