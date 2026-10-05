{{-- Header account area: guest actions, or an avatar menu for signed-in users. --}}
@guest
    <div class="hidden items-center gap-2 sm:flex">
        <a href="{{ route('login') }}" class="inline-flex h-10 items-center gap-2 px-3 text-sm font-semibold text-foreground hover:text-primary">
            <x-icon name="log-in" class="h-4 w-4" /> Sign in
        </a>
        <a href="{{ page_url('submit') }}" class="inline-flex h-10 items-center gap-2 bg-primary px-4 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90">
            <x-icon name="send" class="h-4 w-4" /> Submit a manuscript
        </a>
    </div>
@else
    @php
        $me = auth()->user();
        $picture = $me->authorProfile?->profile_picture;
        $initials = collect(preg_split('/\s+/', trim($me->name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
        $role = $me->isAuthor() ? 'Author' : $me->primaryRole()?->label();
        $links = $me->isAuthor()
            ? [
                ['Dashboard', 'gauge', route('account.dashboard')],
                ['My Submissions', 'inbox', route('account.submissions.index')],
                ['Payments & Invoices', 'receipt', route('account.payments.index')],
                ['Profile', 'user', route('account.profile.edit')],
            ]
            : [['Admin console', 'gauge', route('admin.dashboard')]];
    @endphp
    @if ($me->isAuthor())
        <a href="{{ page_url('submit') }}#submission-form" class="hidden h-10 items-center gap-2 bg-primary px-4 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 md:inline-flex">
            <x-icon name="plus" class="h-4 w-4" /> New submission
        </a>
    @endif
    <div class="relative" x-data="{ menu: false }" @click.outside="menu = false" @keydown.escape.window="menu = false">
        <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-haspopup="menu" aria-label="Account menu"
            class="flex h-10 items-center gap-2.5 border border-border bg-card pr-2 pl-1 transition-colors hover:border-gold">
            @if ($picture)
                <img src="{{ Storage::disk('public')->url($picture) }}" alt="" class="h-8 w-8 rounded-full object-cover">
            @else
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-semibold tracking-wide text-primary-foreground">{{ $initials }}</span>
            @endif
            <span class="hidden max-w-36 truncate text-sm font-semibold capitalize text-foreground md:block">{{ $me->name }}</span>
            <span class="hidden md:block"><x-icon name="chevron-down" class="h-4 w-4 text-muted-foreground" /></span>
        </button>

        <div x-show="menu" x-cloak x-transition.origin.top.right role="menu"
            class="absolute right-0 z-50 mt-2 w-64 border border-border bg-popover py-2 shadow-lg">
            <div class="border-b border-border px-4 pb-3">
                <p class="truncate text-sm font-semibold capitalize">{{ $me->name }}</p>
                <p class="truncate text-xs text-muted-foreground">{{ $me->email }}</p>
                <p class="label-caps mt-1 text-[0.62rem] text-primary">{{ $role }}</p>
            </div>
            @foreach ($links as [$label, $icon, $url])
                <a href="{{ $url }}" role="menuitem" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-secondary hover:text-primary">
                    <x-icon :name="$icon" class="text-muted-foreground" /> {{ $label }}
                </a>
            @endforeach
            @if ($me->isAuthor())
                <a href="{{ page_url('submit') }}#submission-form" role="menuitem" class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium text-primary hover:bg-secondary md:hidden">
                    <x-icon name="plus" /> New submission
                </a>
            @endif
            <div class="mx-4 my-2 border-t border-border"></div>
            <form method="POST" action="{{ $me->canAccessAdmin() ? route('admin.logout') : route('logout') }}">
                @csrf
                <button type="submit" role="menuitem" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-sm text-muted-foreground hover:bg-secondary hover:text-destructive">
                    <x-icon name="log-out" /> Sign out
                </button>
            </form>
        </div>
    </div>
@endguest
