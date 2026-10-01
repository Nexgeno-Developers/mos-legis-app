@props(['title' => null, 'description' => null, 'ogImage' => null])
@php
    $appName = settings('general.application_name');
    $pageTitle = $title ? $title.' | '.$appName : settings('seo_social.default_meta_title', $appName);
    $metaDescription = $description ?: settings('seo_social.default_meta_description');
    $og = $ogImage ?: settings('seo_social.default_og_image');
    $user = auth()->user();
    $nav = [
        ['label' => 'Home', 'route' => 'home', 'children' => [['label' => 'About', 'route' => 'about'], ['label' => 'Editorial Board', 'route' => 'editorial-board']]],
        ['label' => 'Submit', 'route' => 'submit'],
        ['label' => 'Archive', 'route' => 'archive.index'],
        ['label' => 'Blogs', 'route' => 'blogs.index'],
        ['label' => 'Best Paper', 'route' => 'best-paper'],
        ['label' => 'Plagiarism Checker', 'route' => 'plagiarism-checker'],
        ['label' => 'Jobs', 'route' => 'jobs.index', 'children' => [['label' => 'Careers', 'route' => 'careers']]],
        ['label' => 'Patrons', 'route' => 'patrons'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];
    $isActive = fn (array $item) => request()->routeIs($item['route'], str_replace('.index', '', $item['route']).'.*');
    $socials = array_filter([
        'linkedin' => settings('seo_social.linkedin_url'),
        'instagram' => settings('seo_social.instagram_url'),
        'facebook' => settings('seo_social.facebook_url'),
        'youtube' => settings('seo_social.youtube_url'),
        'twitter' => settings('seo_social.x_url'),
    ]);
    $policies = App\Models\Page::published()->where('template', 'layout')
        ->whereNotIn('slug', ['home', 'submit', 'about'])->orderBy('title')->get(['title', 'slug']);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    @if ($metaDescription)<meta name="description" content="{{ $metaDescription }}">@endif
    <meta property="og:title" content="{{ $pageTitle }}">
    @if ($metaDescription)<meta property="og:description" content="{{ $metaDescription }}">@endif
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($og)<meta property="og:image" content="{{ Str::startsWith($og, 'http') ? $og : Storage::disk('public')->url($og) }}">@endif
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ settings('general.favicon') ? Storage::disk('public')->url(settings('general.favicon')) : asset('favicon.png') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen bg-background">
    <x-flash />

    @include('site._header')

    <main>{{ $slot }}</main>

    <footer class="mt-24 border-t border-border bg-secondary">
        <div class="mx-auto max-w-[1200px] px-6 py-14">
            <div class="grid gap-10 md:grid-cols-[1.2fr_1fr_1.4fr]">
                <div>
                    <img src="{{ asset('images/logo-mark.png') }}" alt="" class="h-20 w-20 object-contain">
                    <p class="mt-4 font-display text-lg text-primary">{{ $appName }}</p>
                    <p class="mt-1 text-sm text-muted-foreground italic">Rooted in Tradition. Driven by Justice.</p>
                    @if ($socials)
                        <div class="mt-5 flex gap-3">
                            @foreach ($socials as $network => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" aria-label="{{ ucfirst($network) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center border border-border text-muted-foreground hover:border-gold hover:text-primary">
                                    <x-social-icon :network="$network" />
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div>
                    <h3 class="label-caps text-sm text-foreground">Quick Links</h3>
                    <div class="gold-rule mt-3"></div>
                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach (['home' => 'Home', 'about' => 'About', 'submit' => 'Submit', 'archive.index' => 'Archive', 'editorial-board' => 'Editorial Board', 'careers' => 'Careers', 'contact' => 'Contact'] as $route => $label)
                            <li><a href="{{ route($route) }}" class="text-muted-foreground hover:text-primary">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h3 class="label-caps text-sm text-foreground">Policy &amp; Compliance</h3>
                    <div class="gold-rule mt-3"></div>
                    <ul class="mt-4 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                        @foreach ($policies as $policy)
                            <li><a href="{{ route('pages.show', $policy->slug) }}" class="text-muted-foreground hover:text-primary">{{ $policy->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="gold-rule mt-12"></div>
            <div class="mt-6 flex flex-wrap justify-between gap-4 text-xs text-muted-foreground">
                <p>&copy; {{ now()->year }} {{ $appName }}. All rights reserved.</p>
                <p>{{ settings('general.application_email') }} @if (settings('general.contact_number')) · {{ settings('general.contact_number') }} @endif</p>
            </div>
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
