@props(['variant' => 'outline', 'href' => null, 'type' => 'button', 'icon' => null, 'size' => 'md'])
@php
    $styles = match ($variant) {
        'primary' => 'border-primary bg-primary text-primary-foreground hover:bg-primary/90',
        'gold' => 'border-gold bg-gold text-gold-foreground hover:bg-gold/90',
        'ghost' => 'border-transparent text-muted-foreground hover:text-foreground',
        'danger' => 'border-destructive/40 bg-card text-destructive hover:bg-destructive hover:text-destructive-foreground',
        default => 'border-border bg-card text-foreground hover:border-gold',
    };
    $sizes = $size === 'sm' ? 'px-3 py-1.5 text-[0.7rem]' : 'px-4 py-2.5 text-xs';
    $classes = "label-caps inline-flex items-center justify-center gap-2 border transition-colors disabled:cursor-not-allowed disabled:opacity-50 {$sizes} {$styles}";
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if ($icon)<x-icon :name="$icon" />@endif
        {{ $slot }}
    </button>
@endif
