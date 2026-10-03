@props(['eyebrow' => null, 'title' => null])
<div {{ $attributes }}>
    @if ($eyebrow)<p class="label-caps text-sm text-primary">{{ $eyebrow }}</p>@endif
    @if (filled($title))<h2 @class(['font-display text-3xl text-foreground', 'mt-2' => $eyebrow])>{{ $title }}</h2>@endif
    <div class="gold-rule mt-6"></div>
</div>
