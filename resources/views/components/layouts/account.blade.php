@props(['title', 'heading' => null, 'intro' => null, 'sidebar' => true])
{{-- sidebar=false: focused page without the account menu (e.g. checkout). --}}
@php
    $me = auth()->user();
    $profile = $me->authorProfile;
    $initials = collect(preg_split('/\s+/', trim($me->name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
    $sections = [
        'Manuscripts' => [
            'account.dashboard' => ['Dashboard', 'gauge'],
            'account.submissions.index' => ['My Submissions', 'inbox'],
            'account.payments.index' => ['Payments & Invoices', 'receipt'],
            'account.plagiarism-checks.index' => ['Plagiarism Checks', 'scan-search'],
        ],
        'Contribute' => [
            'account.blogs.index' => ['Blogs Posting', 'file-text'],
            'account.jobs.index' => ['Jobs Posting', 'briefcase'],
        ],
        'Account' => [
            'account.profile.edit' => ['Profile', 'user'],
        ],
    ];
    $isActive = fn (string $route) => request()->routeIs($route, preg_replace('/\.(index|edit)$/', '', $route).'.*');
@endphp
<x-layouts.site :title="$title">
    @unless ($sidebar)
        <div class="mx-auto max-w-[1040px] px-6 py-10">
            <header class="border-b border-border pb-6">
                <a href="{{ route('account.dashboard') }}" class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-primary"><x-icon name="arrow-left" class="h-4 w-4" /> Back to my account</a>
                <p class="label-caps mt-4 flex items-center gap-1.5 text-xs text-primary"><x-icon name="lock" class="h-3.5 w-3.5" /> Secure checkout</p>
                <h1 class="mt-1 font-display text-3xl leading-tight md:text-4xl">{{ $heading ?? $title }}</h1>
                @if ($intro)<p class="measure mt-2 text-base text-muted-foreground">{{ $intro }}</p>@endif
            </header>
            <div class="pt-8">
                {{ $slot }}
            </div>
        </div>
    @else
    <div class="mx-auto max-w-[1200px] px-6 py-10">
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-7 xl:grid-cols-[15rem_minmax(0,1fr)] xl:gap-10">
            {{-- Sidebar --}}
            <aside class="min-w-0 lg:sticky lg:top-40 lg:self-start">
                <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                <div class="flex flex-1 items-center gap-3 border border-border bg-card p-4">
                    @if ($profile?->profile_picture)
                        <img src="{{ Storage::disk('public')->url($profile->profile_picture) }}" alt="" class="h-12 w-12 shrink-0 rounded-full object-cover">
                    @else
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-primary font-display text-xl text-primary-foreground">{{ $initials }}</span>
                    @endif
                    <div class="min-w-0">
                        <p class="truncate font-semibold">{{ $me->name }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ $profile?->authorCategory?->name ?? 'Author' }}</p>
                    </div>
                </div>

                <x-button variant="primary" icon="plus" class="w-full sm:w-auto sm:px-6 md:hidden" :href="route('submit').'#submission-form'">New submission</x-button>
                </div>

                {{-- Mobile: compact horizontal menu without visible scrollbars --}}
                <nav class="no-scrollbar mt-4 -mx-6 flex gap-2 overflow-x-auto px-6 pb-1 lg:hidden" aria-label="Account">
                    @foreach ($sections as $items)
                        @foreach ($items as $route => [$label, $icon])
                            <a href="{{ route($route) }}" @class([
                                'inline-flex shrink-0 items-center gap-2 border px-3 py-2 text-sm whitespace-nowrap',
                                'border-primary bg-primary text-primary-foreground' => $isActive($route),
                                'border-border bg-card hover:border-gold' => ! $isActive($route),
                            ])><x-icon :name="$icon" /> {{ $label }}</a>
                        @endforeach
                    @endforeach
                </nav>

                {{-- Desktop: vertical menu --}}
                <nav class="mt-6 hidden space-y-6 lg:block" aria-label="Account">
                    @foreach ($sections as $group => $items)
                        <div>
                            <p class="label-caps px-3 pb-2 text-[0.65rem] text-muted-foreground">{{ $group }}</p>
                            <ul class="space-y-0.5">
                                @foreach ($items as $route => [$label, $icon])
                                    <li>
                                        <a href="{{ route($route) }}" @class([
                                            'flex items-center gap-3 border-l-2 px-3 py-2.5 text-[0.95rem] transition-colors',
                                            'border-primary bg-card font-semibold text-primary' => $isActive($route),
                                            'border-transparent text-foreground hover:bg-card hover:text-primary' => ! $isActive($route),
                                        ]) @if ($isActive($route)) aria-current="page" @endif>
                                            <x-icon :name="$icon" class="h-[18px] w-[18px]" /> {{ $label }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                    <form method="POST" action="{{ route('logout') }}" class="border-t border-border pt-4">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 px-3 py-2.5 text-[0.95rem] text-muted-foreground hover:text-destructive">
                            <x-icon name="log-out" class="h-[18px] w-[18px]" /> Sign out
                        </button>
                    </form>
                </nav>
            </aside>

            {{-- Content --}}
            <section class="min-w-0">
                <header class="border-b border-border pb-6">
                    <p class="label-caps text-xs text-primary">Author Portal</p>
                    <h1 class="mt-1 font-display text-3xl leading-tight md:text-4xl">{{ $heading ?? $title }}</h1>
                    @if ($intro)<p class="measure mt-2 text-base text-muted-foreground">{{ $intro }}</p>@endif
                </header>
                <div class="pt-8">
                    {{ $slot }}
                </div>
            </section>
        </div>
    </div>
    @endunless
</x-layouts.site>
