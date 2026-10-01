{{-- Public site header. Expects $nav, $isActive and $appName from the site layout. --}}
@php
    $logo = settings('general.application_logo')
        ? Storage::disk('public')->url(settings('general.application_logo'))
        : asset('images/logo-mark.png');
@endphp
<header class="sticky top-0 z-40 bg-card/95 shadow-[0_1px_0_var(--color-border)] backdrop-blur supports-[backdrop-filter]:bg-card/85" x-data="{ open: false }" @keydown.escape.window="open = false">
    {{-- Brand row --}}
    <div class="mx-auto flex h-[4.5rem] max-w-[1200px] items-center justify-between gap-4 px-4 sm:h-20 sm:px-6">
        <a href="{{ route('home') }}" class="group flex min-w-0 items-center gap-3" aria-label="{{ $appName }} — home">
            <img src="{{ $logo }}" alt="" width="56" height="56" class="h-11 w-11 shrink-0 object-contain sm:h-14 sm:w-14">
            <span class="min-w-0 border-l border-gold/50 pl-3">
                <span class="block truncate font-display text-[1.35rem] leading-none font-bold tracking-tight text-primary sm:text-[1.7rem]">{{ $appName }}</span>
                <span class="label-caps mt-1.5 hidden truncate text-[0.62rem] tracking-[0.18em] text-muted-foreground sm:block">Rooted in Tradition · Driven by Justice</span>
            </span>
        </a>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @include('site._account-menu')
            <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="mobile-nav" aria-label="Menu"
                class="inline-flex h-10 w-10 items-center justify-center border border-border text-foreground hover:border-gold hover:text-primary lg:hidden">
                <span x-show="!open"><x-icon name="menu" class="h-5 w-5" /></span>
                <span x-show="open" x-cloak><x-icon name="x" class="h-5 w-5" /></span>
            </button>
        </div>
    </div>

    {{-- Primary navigation (desktop) --}}
    <div class="hidden border-t border-border bg-background lg:block">
        <nav class="mx-auto max-w-[1200px] px-6" aria-label="Main">
            <ul class="-mx-3 flex items-stretch justify-between">
                @foreach ($nav as $item)
                    @php $active = $isActive($item) || collect($item['children'] ?? [])->contains(fn ($c) => request()->routeIs($c['route'])); @endphp
                    <li class="group relative">
                        <a href="{{ route($item['route']) }}" @if ($active) aria-current="page" @endif @class([
                            'relative flex h-12 items-center gap-1 px-3 text-[0.78rem] font-medium tracking-[0.08em] whitespace-nowrap uppercase transition-colors xl:px-4 xl:text-[0.8rem]',
                            'after:absolute after:inset-x-3 after:bottom-0 after:h-[2px] after:bg-primary after:transition-transform',
                            'text-primary after:scale-x-100' => $active,
                            'text-foreground/85 after:scale-x-0 hover:text-primary hover:after:scale-x-100' => ! $active,
                        ])>
                            {{ $item['label'] }}
                            @isset($item['children'])<x-icon name="chevron-down" class="h-3.5 w-3.5 opacity-60 transition-transform group-hover:rotate-180" />@endisset
                        </a>
                        @isset($item['children'])
                            <div class="invisible absolute top-full left-0 z-30 min-w-52 translate-y-1 border border-border bg-popover py-2 opacity-0 shadow-lg transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
                                <a href="{{ route($item['route']) }}" class="block px-4 py-2 text-sm hover:bg-secondary hover:text-primary">{{ $item['label'] }}</a>
                                @foreach ($item['children'] as $child)
                                    <a href="{{ route($child['route']) }}" @class(['block px-4 py-2 text-sm hover:bg-secondary hover:text-primary', 'text-primary' => request()->routeIs($child['route'])])>{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        @endisset
                    </li>
                @endforeach
            </ul>
        </nav>
    </div>

    {{-- Mobile navigation --}}
    <nav id="mobile-nav" x-show="open" x-cloak x-transition.opacity class="max-h-[calc(100vh-4.5rem)] overflow-y-auto border-t border-border bg-card lg:hidden" aria-label="Mobile">
        <ul class="divide-y divide-border px-4 sm:px-6">
            @foreach ($nav as $item)
                <li>
                    <a href="{{ route($item['route']) }}" @class(['flex items-center justify-between py-3.5 text-[0.95rem] font-semibold tracking-[0.04em] uppercase', 'text-primary' => $isActive($item), 'text-foreground/80' => ! $isActive($item)])>
                        {{ $item['label'] }} <x-icon name="chevron-right" class="h-4 w-4 opacity-40" />
                    </a>
                    @foreach ($item['children'] ?? [] as $child)
                        <a href="{{ route($child['route']) }}" class="block pb-3 pl-4 text-sm text-muted-foreground hover:text-primary">{{ $child['label'] }}</a>
                    @endforeach
                </li>
            @endforeach
        </ul>
        @guest
            <div class="grid grid-cols-2 gap-3 border-t border-border p-4 sm:px-6">
                <x-button icon="log-in" :href="route('login')">Sign in</x-button>
                <x-button variant="primary" icon="send" :href="route('submit')">Submit</x-button>
            </div>
        @endguest
    </nav>
</header>
