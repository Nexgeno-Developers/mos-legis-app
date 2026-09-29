@props(['title' => null, 'description' => null])
<section {{ $attributes->merge(['class' => 'border border-border bg-card']) }}>
    @if ($title)
        <header class="flex flex-wrap items-center justify-between gap-3 border-b border-border px-6 py-4">
            <div>
                <h2 class="font-display text-xl text-foreground">{{ $title }}</h2>
                @if ($description)<p class="text-sm text-muted-foreground">{{ $description }}</p>@endif
            </div>
            @isset($actions)<div class="flex gap-2">{{ $actions }}</div>@endisset
        </header>
    @endif
    <div class="p-6">{{ $slot }}</div>
</section>
