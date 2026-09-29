@props(['label', 'value', 'hint' => null, 'href' => null])
<div {{ $attributes->merge(['class' => 'bg-card p-6']) }}>
    <p class="label-caps text-xs text-muted-foreground">{{ $label }}</p>
    <p class="mt-2 font-display text-3xl text-foreground">
        @if ($href)<a href="{{ $href }}" class="hover:text-primary">{{ $value }}</a>@else{{ $value }}@endif
    </p>
    @if ($hint)<p class="mt-1 text-sm text-muted-foreground">{{ $hint }}</p>@endif
</div>
