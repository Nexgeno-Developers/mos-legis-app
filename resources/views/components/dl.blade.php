@props(['items' => []])
{{-- Definition list for detail views: ['Label' => 'value', ...] --}}
<dl {{ $attributes->merge(['class' => 'grid gap-x-8 gap-y-4 sm:grid-cols-2']) }}>
    @foreach ($items as $label => $value)
        <div>
            <dt class="label-caps text-xs text-muted-foreground">{{ $label }}</dt>
            <dd class="mt-0.5 text-base text-foreground">{!! $value === null || $value === '' ? '—' : $value !!}</dd>
        </div>
    @endforeach
    {{ $slot }}
</dl>
