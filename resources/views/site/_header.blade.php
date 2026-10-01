{{-- Public site header. Expects $headerMenu (App\Support\SiteMenu, managed in Admin → Menus) and $appName from the site layout. --}}
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
                @foreach ($headerMenu as $item)
                    @php
                        $active = $item['active'];
                        $linkClass = [
                            'relative flex h-12 items-center gap-1 px-3 text-[0.78rem] font-medium tracking-[0.08em] whitespace-nowrap uppercase transition-colors xl:px-4 xl:text-[0.8rem]',
                            'after:absolute after:inset-x-3 after:bottom-0 after:h-[2px] after:bg-primary after:transition-transform',
                            'text-primary after:scale-x-100' => $active,
                            'text-foreground/85 after:scale-x-0 hover:text-primary hover:after:scale-x-100' => ! $active,
                        ];
                    @endphp
                    @if ($item['children'])
                        <li class="group relative" x-data="{ menu: false }" @pointerenter="if ($event.pointerType === 'mouse') menu = true" @pointerleave="if ($event.pointerType === 'mouse') menu = false" @click.outside="menu = false" @keydown.escape="menu = false; $refs.toggle.focus()" @focusout="if (! $el.contains($event.relatedTarget)) menu = false">
                            <button type="button" x-ref="toggle" @click="menu = $event.detail === 0 ? ! menu : true" :aria-expanded="menu" aria-haspopup="true" @class($linkClass)>
                                {{ $item['label'] }}
                                <span class="flex opacity-60 transition-transform" :class="menu && 'rotate-180'"><x-icon name="chevron-down" class="h-3.5 w-3.5" /></span>
                            </button>
                            <div x-show="menu" x-cloak x-transition.opacity.duration.150ms class="absolute top-full left-0 z-30 min-w-56 border border-border border-t-2 border-t-primary bg-popover py-2 shadow-lg">
                                @foreach ($item['children'] as $child)
                                    @php $childActive = $child['active']; @endphp
                                    <a href="{{ $child['url'] }}" @if ($child['new_tab']) target="_blank" rel="noopener" @endif @if ($childActive) aria-current="page" @endif @class([
                                        'block border-l-2 px-4 py-2.5 text-sm whitespace-nowrap transition-colors hover:bg-secondary hover:text-primary',
                                        'border-primary bg-secondary/60 font-medium text-primary' => $childActive,
                                        'border-transparent' => ! $childActive,
                                    ])>{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        </li>
                    @else
                        <li class="relative">
                            <a href="{{ $item['url'] }}" @if ($item['new_tab']) target="_blank" rel="noopener" @endif @if ($active) aria-current="page" @endif @class($linkClass)>{{ $item['label'] }}</a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>
    </div>

    {{-- Mobile navigation --}}
    <nav id="mobile-nav" x-show="open" x-cloak x-transition.opacity class="max-h-[calc(100vh-4.5rem)] overflow-y-auto border-t border-border bg-card lg:hidden" aria-label="Mobile">
        <ul class="divide-y divide-border px-4 sm:px-6">
            @foreach ($headerMenu as $item)
                <li>
                    @if ($item['children'])
                        <p class="label-caps pt-4 pb-1 text-xs text-muted-foreground">{{ $item['label'] }}</p>
                        @foreach ($item['children'] as $child)
                            <a href="{{ $child['url'] }}" @if ($child['new_tab']) target="_blank" rel="noopener" @endif @class(['flex items-center justify-between py-3 text-[0.95rem] font-semibold tracking-[0.04em] uppercase', 'text-primary' => $child['active'], 'text-foreground/80' => ! $child['active']])>
                                {{ $child['label'] }} <x-icon name="chevron-right" class="h-4 w-4 opacity-40" />
                            </a>
                        @endforeach
                    @else
                        <a href="{{ $item['url'] }}" @if ($item['new_tab']) target="_blank" rel="noopener" @endif @class(['flex items-center justify-between py-3.5 text-[0.95rem] font-semibold tracking-[0.04em] uppercase', 'text-primary' => $item['active'], 'text-foreground/80' => ! $item['active']])>
                            {{ $item['label'] }} <x-icon name="chevron-right" class="h-4 w-4 opacity-40" />
                        </a>
                    @endif
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
