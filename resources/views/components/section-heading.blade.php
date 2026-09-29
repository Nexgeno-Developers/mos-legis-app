@props(['eyebrow' => null, 'title'])
<div {{ $attributes }}>
    @if ($eyebrow)<p class="label-caps text-sm text-primary">{{ $eyebrow }}</p>@endif
    <h2 class="mt-2 font-display text-3xl text-foreground">{{ $title }}</h2>
    <div class="gold-rule mt-6"></div>
</div>
