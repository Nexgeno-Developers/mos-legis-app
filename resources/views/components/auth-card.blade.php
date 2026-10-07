@props(['title', 'intro' => null])
<section class="border-b border-border bg-secondary">
    <div class="mx-auto max-w-[1200px] px-6 py-14">
        <div class="mx-auto max-w-lg border border-border bg-card p-8 shadow-sm md:p-10">
            <p class="label-caps text-sm text-primary">Author Portal</p>
            <h1 class="mt-2 font-display text-3xl text-foreground">{{ $title }}</h1>
            <div class="gold-rule my-5"></div>
            @if ($intro)<p class="text-base text-muted-foreground">{{ $intro }}</p>@endif
            @if (session('status'))@php(app()->instance('flash.status-inline', true))<div class="mt-5 border-l-2 border-gold/60 bg-secondary px-4 py-3 text-base">{{ session('status') }}</div>@endif
            {{ $slot }}
        </div>
    </div>
</section>
