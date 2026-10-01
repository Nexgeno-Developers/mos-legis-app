@props(['label' => null, 'name' => null, 'hint' => null, 'required' => false])
@php $errorKey = $name ? str_replace(['[', ']'], ['.', ''], $name) : null; @endphp
<div {{ $attributes->merge(['class' => 'flex flex-col gap-1.5']) }} data-field>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif class="label-caps text-xs text-muted-foreground">
            {{ $label }}@if ($required)<span class="text-primary"> *</span>@endif
        </label>
    @endif
    {{ $slot }}
    @if ($hint)<p class="text-sm text-muted-foreground">{{ $hint }}</p>@endif
    @if ($errorKey)
        @error($errorKey)<p class="text-sm text-destructive" role="alert" data-server-error>{{ $message }}</p>@enderror
    @endif
</div>
