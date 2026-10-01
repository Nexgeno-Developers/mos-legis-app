@props(['title' => null])
@php
    $user = auth()->user();
    $navigation = \App\Support\AdminNavigation::for($user);
    $current = \App\Support\AdminNavigation::currentLabel($user);
    $initials = collect(explode(' ', $user->name))->filter()->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $current }} | {{ settings('general.application_name') }} Console</title>
    <link rel="icon" href="{{ settings('general.favicon') ? Storage::disk('public')->url(settings('general.favicon')) : asset('favicon.png') }}">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="min-h-screen" x-data="{ collapsed: false, mobileOpen: false }">
    <x-flash />

    <div class="flex min-h-screen w-full bg-background">
        {{-- Sidebar --}}
        <aside :class="[collapsed ? 'lg:w-20' : 'lg:w-72', mobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']"
            class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-border bg-sidebar transition-all lg:sticky lg:top-0 lg:h-screen">
            <div class="flex h-20 items-center gap-3 border-b border-border px-5">
                <img src="{{ asset('images/logo-mark.png') }}" alt="" class="h-12 w-12 shrink-0 object-contain">
                <div x-show="!collapsed" class="min-w-0">
                    <p class="truncate font-display text-xl leading-none text-primary">{{ settings('general.application_name') }}</p>
                    <p class="label-caps mt-1.5 text-[0.65rem] text-muted-foreground">Superadmin Console</p>
                </div>
                <button type="button" @click="collapsed = !collapsed" class="ml-auto hidden text-muted-foreground hover:text-foreground lg:block" aria-label="Collapse sidebar">
                    <x-icon name="panel-left" class="h-5 w-5" />
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto py-6">
                @foreach ($navigation as $group => $items)
                    <div class="mb-6">
                        <p x-show="!collapsed" class="label-caps px-5 pb-2 text-[0.65rem] text-muted-foreground">{{ $group }}</p>
                        <ul>
                            @foreach ($items as $item)
                                <li>
                                    <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                                        @class([
                                            'flex items-center gap-3 border-l-2 px-5 py-2.5 text-[0.95rem] transition-colors',
                                            'border-primary bg-background text-primary' => $item['active'],
                                            'border-transparent text-foreground hover:bg-background/70 hover:text-primary' => ! $item['active'],
                                        ])>
                                        <x-icon :name="$item['icon']" class="h-[18px] w-[18px]" />
                                        <span x-show="!collapsed" class="flex-1 truncate">{{ $item['label'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>
        </aside>
        <div x-show="mobileOpen" x-cloak @click="mobileOpen = false" class="fixed inset-0 z-40 bg-foreground/40 lg:hidden"></div>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 flex h-20 items-center gap-4 border-b border-border bg-background/95 px-5 backdrop-blur md:px-8">
                <button type="button" @click="mobileOpen = !mobileOpen" class="text-foreground lg:hidden" aria-label="Toggle console menu">
                    <x-icon name="menu" class="h-6 w-6" />
                </button>
                <div class="min-w-0 flex-1">
                    <p class="label-caps text-[0.65rem] text-muted-foreground">{{ $user->isSuperadmin() ? 'Superadmin Console' : 'Reviewer Console' }}</p>
                    <p class="truncate font-display text-2xl leading-tight text-foreground">{{ $current }}</p>
                </div>

                @if (Route::has('admin.submissions.index') && auth()->user()->can('submissions.view'))
                    <form method="GET" action="{{ route('admin.submissions.index') }}" class="hidden items-center gap-2 border border-border bg-card px-3 py-2 md:flex">
                        <x-icon name="search" class="text-muted-foreground" />
                        <input type="search" name="search" placeholder="Search submissions by ID or title…" class="w-56 bg-transparent text-sm focus:outline-none">
                    </form>
                @endif

                <a href="{{ route('home') }}" target="_blank" class="text-muted-foreground hover:text-foreground" title="View website">
                    <x-icon name="external-link" class="h-5 w-5" />
                </a>

                <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 border-l border-border pl-4">
                    <span class="hidden text-right sm:block">
                        <span class="block text-sm leading-tight text-foreground">{{ $user->name }}</span>
                        <span class="label-caps block text-[0.65rem] text-muted-foreground">{{ $user->primaryRole()?->label() }}</span>
                    </span>
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary font-display text-lg text-primary-foreground">{{ $initials }}</span>
                </a>

                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-muted-foreground hover:text-foreground" aria-label="Log out" title="Log out">
                        <x-icon name="log-out" class="h-5 w-5" />
                    </button>
                </form>
            </header>

            <main class="flex-1 px-5 py-8 md:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>
