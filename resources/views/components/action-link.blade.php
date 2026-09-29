@props(['href' => null, 'icon' => null])
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 text-sm text-foreground hover:text-primary']) }}>
        @if ($icon)<x-icon :name="$icon" />@endif<span>{{ $slot }}</span>
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center gap-1 text-sm text-foreground hover:text-primary']) }}>
        @if ($icon)<x-icon :name="$icon" />@endif<span>{{ $slot }}</span>
    </button>
@endif
