@props(['title', 'description' => null])
<div class="flex flex-wrap items-end justify-between gap-6 border-b border-border pb-6">
    <div>
        <h1 class="font-display text-3xl leading-tight text-foreground">{{ $title }}</h1>
        @if ($description)<p class="measure mt-2 text-base text-muted-foreground">{{ $description }}</p>@endif
    </div>
    @isset($actions)<div class="flex flex-wrap gap-3">{{ $actions }}</div>@endisset
</div>
