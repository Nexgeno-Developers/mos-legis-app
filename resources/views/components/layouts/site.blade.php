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
        ['label' => 'Best Paper Winners', 'route' => 'best-paper'],
        ['label' => 'Plagiarism Checker', 'route' => 'plagiarism-checker'],
        ['label' => 'Job Postings', 'route' => 'jobs.index', 'children' => [['label' => 'Careers', 'route' => 'careers']]],
        ['label' => 'Patron Acknowledgements', 'route' => 'patrons'],
        ['label' => 'Contact Us', 'route' => 'contact'],
    ];
    $nav[] = $user?->isAuthor()
        ? ['label' => 'My Account', 'route' => 'account.dashboard']
        : ['label' => 'Author Portal', 'route' => 'login'];
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

    <header class="sticky top-0 z-40 border-b border-border bg-background/95 backdrop-blur" x-data="{ open: false }">
        <div class="mx-auto grid h-24 max-w-[1200px] grid-cols-[minmax(0,1fr)_auto] items-center gap-4 px-6 sm:h-28">
            <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-1">
                <img src="{{ settings('general.application_logo') ? Storage::disk('public')->url(settings('general.application_logo')) : asset('images/logo.png') }}"
                    alt="{{ $appName }}" class="h-20 w-20 shrink-0 object-contain mix-blend-multiply sm:h-24 sm:w-24">
                <span class="flex min-w-0 flex-col">
                    <span class="truncate font-display text-2xl leading-none font-bold tracking-tight text-primary sm:text-[2.2rem]">{{ $appName }}</span>
                    <span class="label-caps mt-1 hidden truncate text-[0.68rem] text-muted-foreground sm:block">Rooted in Tradition. Driven by Justice.</span>
                </span>
            </a>
            <div class="flex items-center gap-3">
                @auth
                    <form method="POST" action="{{ auth()->user()->canAccessAdmin() ? route('admin.logout') : route('logout') }}" class="hidden lg:block">
                        @csrf
                        <button type="submit" class="label-caps text-xs text-muted-foreground hover:text-primary">Sign out</button>
                    </form>
                @endauth
                <button type="button" @click="open = !open" aria-label="Toggle navigation"
                    class="inline-flex h-10 w-10 items-center justify-center border border-border text-gold hover:border-primary hover:text-primary lg:hidden">
                    <x-icon name="menu" class="h-5 w-5" />
                </button>
            </div>
        </div>

        <div class="hidden border-t border-border/70 bg-secondary/40 lg:block">
            <nav class="mx-auto flex max-w-[1200px] items-stretch justify-between px-4" aria-label="Main">
                @foreach ($nav as $item)
                    <div class="group relative">
                        <a href="{{ route($item['route']) }}" @class([
                            'label-caps relative inline-flex items-center whitespace-nowrap px-2.5 py-3.5 text-[0.78rem] tracking-[0.08em] transition-colors after:absolute after:inset-x-2.5 after:bottom-0 after:h-px after:bg-primary after:transition-transform hover:text-primary hover:after:scale-x-100 xl:px-3.5 xl:text-[0.86rem]',
                            'text-primary after:scale-x-100' => $isActive($item),
                            'text-gold after:scale-x-0' => ! $isActive($item),
                        ])>{{ $item['label'] }}</a>
                        @isset($item['children'])
                            <div class="invisible absolute left-0 top-full z-20 min-w-48 border border-border bg-popover py-1 opacity-0 shadow-md transition-opacity group-hover:visible group-hover:opacity-100">
                                @foreach ($item['children'] as $child)
                                    <a href="{{ route($child['route']) }}" class="label-caps block px-4 py-2.5 text-[0.85rem] text-gold hover:bg-secondary hover:text-primary">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        @endisset
                    </div>
                @endforeach
            </nav>
        </div>

        <nav x-show="open" x-cloak class="border-t border-border bg-background px-6 py-3 lg:hidden" aria-label="Mobile">
            @foreach ($nav as $item)
                <div class="border-b border-border/60 last:border-0">
                    <a href="{{ route($item['route']) }}" class="label-caps block py-3 text-base text-gold hover:text-primary">{{ $item['label'] }}</a>
                    @foreach ($item['children'] ?? [] as $child)
                        <a href="{{ route($child['route']) }}" class="label-caps block py-2 pl-4 text-base text-gold/85 hover:text-primary">{{ $child['label'] }}</a>
                    @endforeach
                </div>
            @endforeach
            @auth
                <form method="POST" action="{{ auth()->user()->canAccessAdmin() ? route('admin.logout') : route('logout') }}" class="py-3">
                    @csrf
                    <button type="submit" class="label-caps text-base text-muted-foreground">Sign out</button>
                </form>
            @endauth
        </nav>
    </header>

    <main>{{ $slot }}</main>

    <footer class="mt-24 border-t border-border bg-secondary">
        <div class="mx-auto max-w-[1200px] px-6 py-14">
            <div class="grid gap-10 md:grid-cols-[1.2fr_1fr_1.4fr]">
                <div>
                    <img src="{{ asset('images/logo.png') }}" alt="" class="h-20 w-20 object-contain mix-blend-multiply">
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
