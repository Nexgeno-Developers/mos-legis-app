@props(['name', 'label', 'placeholder' => null, 'width' => 'sm:w-44', 'type' => 'search'])
{{-- Small labelled text filter (e.g. Action, Location, Manuscript ID). --}}
<label class="flex w-full flex-col gap-1.5 {{ $width }}">
    <span class="label-caps text-xs text-muted-foreground">{{ $label }}</span>
    <input type="{{ $type }}" name="{{ $name }}" value="{{ request($name) }}" @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        {{ $attributes->merge(['class' => 'field-input']) }}>
</label>
