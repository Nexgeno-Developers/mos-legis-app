@props(['title', 'intro' => null, 'crumbs' => [], 'eyebrow' => null])
{{--
    Compact page header with a breadcrumb trail (Home › …parents › current page).
    $crumbs: optional parent links as ['Label' => url]. $eyebrow is accepted for
    backward compatibility but no longer rendered — the breadcrumb replaces it.
--}}
@php
    $trail = ['Home' => route('home')] + $crumbs;
@endphp
<section class="border-b border-border bg-secondary/70">
    <div class="mx-auto max-w-[1200px] px-4 py-6 sm:px-6 md:py-8">
        <nav aria-label="Breadcrumb">
            <ol class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-muted-foreground" itemscope itemtype="https://schema.org/BreadcrumbList">
                @foreach ($trail as $label => $url)
                    <li class="flex items-center gap-1.5" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        <a href="{{ $url }}" class="hover:text-primary hover:underline" itemprop="item"><span itemprop="name">{{ $label }}</span></a>
                        <meta itemprop="position" content="{{ $loop->iteration }}">
                        <x-icon name="chevron-right" class="h-3 w-3 opacity-60" />
                    </li>
                @endforeach
                <li class="min-w-0 truncate font-medium text-foreground" aria-current="page" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <span itemprop="name">{{ Str::limit($title, 70) }}</span>
                    <meta itemprop="position" content="{{ count($trail) + 1 }}">
                </li>
            </ol>
        </nav>
        <h1 class="mt-2 font-display text-2xl leading-tight text-foreground md:text-[2rem]">{{ $title }}</h1>
        @if ($intro)<p class="measure mt-1.5 text-base leading-relaxed text-muted-foreground">{{ $intro }}</p>@endif
        {{ $slot }}
    </div>
</section>
