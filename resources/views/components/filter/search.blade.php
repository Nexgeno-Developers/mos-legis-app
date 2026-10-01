@props(['name' => 'search', 'placeholder' => 'Search…', 'label' => 'Search'])
{{-- Labelled like every other filter so all filter controls share one baseline. --}}
<label class="flex w-full flex-col gap-1.5 sm:w-auto sm:min-w-[16rem] sm:flex-1 md:max-w-sm">
    <span class="label-caps text-xs text-muted-foreground">{{ $label }}</span>
    <span class="relative block" data-input-wrap>
        <x-icon name="search" class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
        <input type="search" name="{{ $name }}" value="{{ request($name) }}" placeholder="{{ $placeholder }}"
            {{ $attributes->merge(['class' => 'field-input pl-9!']) }}>
    </span>
</label>
