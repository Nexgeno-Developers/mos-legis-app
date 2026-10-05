@props(['title' => null, 'description' => null, 'ogImage' => null])
@php
    $appName = settings('general.application_name');
    // CMS SEO titles often already end with the site name; don't append it twice.
    $pageTitle = $title ? (Str::contains($title, $appName) ? $title : $title.' | '.$appName) : settings('seo_social.default_meta_title', $appName);
    $metaDescription = $description ?: settings('seo_social.default_meta_description');
    $og = $ogImage ?: settings('seo_social.default_og_image');
    $user = auth()->user();
    // Header and footer menus are managed in Admin → Menus.
    $headerMenu = App\Support\SiteMenu::for(App\Enums\MenuLocation::Header);
    $footerMenu = App\Support\SiteMenu::for(App\Enums\MenuLocation::Footer);
    $socials = array_filter([
        'linkedin' => settings('seo_social.linkedin_url'),
        'instagram' => settings('seo_social.instagram_url'),
        'facebook' => settings('seo_social.facebook_url'),
        'youtube' => settings('seo_social.youtube_url'),
        'twitter' => settings('seo_social.x_url'),
    ]);
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

    {{-- No gap on the home page: its closing band sits directly above the footer. --}}
    <footer @class(['border-t border-border bg-secondary', 'mt-24' => ! request()->routeIs('home')])>
        <div class="mx-auto max-w-[1200px] px-6 py-14">
            @php
                // Footer columns: each group is a column; loose top-level links share one untitled column.
                $looseLinks = array_values(array_filter($footerMenu, fn ($item) => ! $item['children']));
                $columns = array_values(array_filter($footerMenu, fn ($item) => $item['children']));
                if ($looseLinks) {
                    array_unshift($columns, ['label' => null, 'children' => $looseLinks]);
                }
            @endphp
            <div class="flex flex-wrap gap-x-10 gap-y-10">
                <div class="w-full md:w-64 md:shrink-0">
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
                @foreach ($columns as $column)
                    @php $wide = count($column['children']) > 7; @endphp
                    <div @class(['min-w-40 flex-1', 'basis-full sm:basis-auto sm:flex-[2]' => $wide])>
                        <h3 class="label-caps text-sm text-foreground">{{ $column['label'] ?? 'Links' }}</h3>
                        <div class="gold-rule mt-3"></div>
                        <ul @class(['mt-4 gap-2 text-sm', 'grid grid-cols-1 sm:grid-cols-2' => $wide, 'space-y-2' => ! $wide])>
                            @foreach ($column['children'] as $link)
                                <li><a href="{{ $link['url'] }}" @if ($link['new_tab']) target="_blank" rel="noopener" @endif @if ($link['active']) aria-current="page" @endif
                                    @class(['hover:text-primary', 'text-primary' => $link['active'], 'text-muted-foreground' => ! $link['active']])>{{ $link['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
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
