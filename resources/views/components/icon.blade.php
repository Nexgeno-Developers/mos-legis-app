@props(['name'])
<i data-lucide="{{ $name }}" {{ $attributes->merge(['class' => 'inline-block h-4 w-4 shrink-0']) }} aria-hidden="true"></i>
