@props(['eyebrow' => null, 'title', 'intro' => null])
<section class="border-b border-border bg-secondary">
    <div class="mx-auto max-w-[1200px] px-6 py-16 md:py-20">
        @if ($eyebrow)<p class="label-caps text-sm text-primary">{{ $eyebrow }}</p>@endif
        <h1 class="mt-3 font-display text-4xl leading-[1.15] text-foreground md:text-5xl">{{ $title }}</h1>
        <div class="gold-rule my-6 max-w-md"></div>
        @if ($intro)<p class="measure text-lg text-muted-foreground">{{ $intro }}</p>@endif
        {{ $slot }}
    </div>
</section>
