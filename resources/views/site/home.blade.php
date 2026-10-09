@php
    // All wording comes from Admin → Pages → Home ("Home" template); an empty field hides its element.
    // {fee} and {threshold} are filled in from Settings.
    $appName = settings('general.application_name');
    $fill = fn (?string $text) => strtr((string) $text, [
        '{fee}' => money(settings()->float('manuscript.plagiarism_prescreening_fee')),
        '{threshold}' => rtrim(rtrim(number_format(settings()->float('manuscript.plagiarism_max_similarity_percent'), 2), '0'), '.'),
    ]);
    $m = fn (string $key) => $page ? $fill($page->meta($key)) : '';
    $rows = fn (string $key) => collect($page?->meta($key) ?: [])->filter(fn ($row) => implode('', (array) $row) !== '')->values();
    $features = $rows('features');
    $steps = $rows('steps');
    $featureIcons = ['shield-check', 'scan-search', 'badge-indian-rupee', 'award', 'badge-check', 'book-open'];
@endphp
<x-layouts.site :title="null" :description="$page?->seo_description">
    {{-- 1. Hero: who we are + the two main actions --}}
    <section class="relative overflow-hidden border-b border-border bg-secondary">
        <div class="mx-auto grid max-w-[1200px] items-center gap-10 px-4 py-[30px] md:py-[70px] sm:px-6 lg:grid-cols-[1.3fr_1fr]">
            <div>
                @if ($m('hero_label'))<p class="label-caps text-sm text-primary">{{ $m('hero_label') }}</p>@endif
                @if ($m('hero_heading'))<h1 class="mt-4 font-display text-4xl leading-[1.1] sm:text-5xl lg:text-[3.4rem]">{!! nl2br(e($m('hero_heading'))) !!}</h1>@endif
                <div class="gold-rule my-6 max-w-md"></div>
                @if ($page?->excerpt)<p class="measure text-lg leading-relaxed text-muted-foreground">{{ $page->excerpt }}</p>@endif

                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($m('hero_primary'))<a href="{{ page_url('submit') }}" class="inline-flex h-10 items-center gap-2 border border-primary bg-primary px-4 text-xs font-semibold sm:h-12 sm:px-6 sm:text-sm text-primary-foreground hover:bg-primary/90">{{ $m('hero_primary') }} <x-icon name="arrow-right" /></a>@endif
                    @if ($m('hero_secondary'))<a href="{{ route('archive.index') }}" class="inline-flex h-10 items-center gap-2 border border-gold bg-card px-4 text-xs font-semibold sm:h-12 sm:px-6 sm:text-sm hover:bg-gold/10"><x-icon name="book-open" /> {{ $m('hero_secondary') }}</a>@endif
                </div>

            </div>

            <div class="hidden justify-center lg:flex">
                <img src="{{ asset('images/logo-mark.png') }}" alt="{{ $appName }} emblem" class="w-full max-w-[22rem] object-contain drop-shadow-sm">
            </div>
        </div>
    </section>

    {{-- 2. Journal at a glance --}}
    <section class="border-b border-border bg-card">
        <dl class="mx-auto grid max-w-[1200px] grid-cols-2 divide-border px-4 sm:px-6 md:grid-cols-4 md:divide-x">
            @foreach ([['articles', $m('stat_articles'), 'file-text'], ['authors', $m('stat_authors'), 'users'], ['categories', $m('stat_categories'), 'book-open'], ['awards', $m('stat_awards'), 'award']] as [$key, $label, $icon])
                <div class="flex flex-col items-center gap-3 px-2 lg:py-6 py-3 text-center md:flex-row md:justify-center md:px-6 md:text-left">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-secondary text-primary"><x-icon :name="$icon" class="h-5 w-5" /></span>
                    <div>
                        <dd class="font-display text-2xl leading-none">{{ number_format($stats[$key]) }}</dd>
                        <dt class="mt-1 text-xs text-muted-foreground">{{ $label }}</dt>
                    </div>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- 3. Why publish with us --}}
    @if ($features->isNotEmpty())
    <section class="bg-background">
        <div class="mx-auto max-w-[1200px] px-4 py-[30px] md:py-[70px] sm:px-6">
        <div class="max-w-2xl">
            <x-section-heading :eyebrow="$m('features_label')" :title="$m('features_heading')" />
        </div>
        <div class="lg:mt-10 mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($features as $feature)
                <div class="border border-border bg-card p-6 transition hover:border-gold">
                    <span class="grid h-11 w-11 place-items-center border border-gold/60 text-primary"><x-icon :name="$featureIcons[$loop->index % count($featureIcons)]" class="h-5 w-5" /></span>
                    @if (filled($feature['title'] ?? null))<h3 class="mt-4 font-display text-xl">{{ $fill($feature['title']) }}</h3>@endif
                    @if (filled($feature['text'] ?? null))<p class="mt-2 text-sm leading-relaxed text-muted-foreground">{{ $fill($feature['text']) }}</p>@endif
                </div>
            @endforeach
        </div>
        </div>
    </section>
    @endif

    {{-- 4. How it works --}}
    @if ($steps->isNotEmpty())
    <section class="relative overflow-hidden border-y border-gold/40 bg-foreground text-background">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, var(--color-gold) 1px, transparent 0); background-size: 22px 22px;"></div>
        <div class="relative mx-auto max-w-[1200px] px-4 py-[30px] md:py-[70px] sm:px-6">
            <div class="mx-auto max-w-2xl text-center">
                @if ($m('steps_label'))<p class="label-caps text-sm text-gold">{{ $m('steps_label') }}</p>@endif
                @if (filled($m('steps_heading')))<h2 class="mt-3 font-display text-3xl text-background md:text-4xl">{{ $m('steps_heading') }}</h2>@endif
                <div class="gold-rule mx-auto mt-6 max-w-xs"></div>
            </div>
            <div x-data="{ i: 0 }" class="mt-6 sm:mt-14">
            <ol x-ref="track" @scroll.passive="i = Math.round($refs.track.scrollLeft / ($refs.track.scrollWidth / {{ $steps->count() }}))" class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] md:mx-0 md:grid md:snap-none md:grid-cols-5 md:gap-0 md:overflow-visible md:px-0 md:pb-0 [&::-webkit-scrollbar]:hidden">
                @foreach ($steps as $step)
                    <li class="group relative w-[78%] shrink-0 snap-center border border-gold/30 bg-background/5 px-4 py-8 text-center md:w-auto md:border-0 md:bg-transparent md:px-2 md:py-0 lg:px-5">
                        @unless ($loop->last)
                            <span aria-hidden="true" class="absolute left-1/2 top-7 hidden h-px w-full bg-gradient-to-r from-gold via-gold/50 to-gold/50 md:block"></span>
                        @endunless
                        <span class="relative z-10 mx-auto grid h-14 w-14 place-items-center rounded-full border-2 border-gold bg-foreground font-display text-2xl text-gold shadow-[0_0_0_6px_var(--color-foreground)] transition duration-300 group-hover:scale-110 group-hover:bg-primary group-hover:text-primary-foreground {{ $loop->last ? '!bg-gold !text-gold-foreground' : '' }}">{{ $loop->iteration }}</span>
                        @if (filled($step['title'] ?? null))<p class="mt-6 font-display text-xl text-background">{{ $fill($step['title']) }}</p>@endif
                        @if (filled($step['text'] ?? null))<p class="mx-auto mt-2 max-w-[15rem] text-sm leading-relaxed text-background/70">{{ $fill($step['text']) }}</p>@endif
                    </li>
                @endforeach
            </ol>
            <div class="mt-5 flex justify-center gap-2 md:hidden">
                @foreach ($steps as $step)
                    <button type="button" aria-label="Go to step {{ $loop->iteration }}" @click="$refs.track.children[{{ $loop->index }}].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' })" class="h-2 rounded-full transition-all duration-300" :class="i === {{ $loop->index }} ? 'w-6 bg-gold' : 'w-2 bg-gold/30'"></button>
                @endforeach
            </div>
            </div>
            @if ($m('steps_link'))
                <div class="mt-8 text-center">
                    <a href="{{ page_url('submit') }}#process" class="inline-flex items-center gap-2 border border-gold px-4 py-2 text-xs font-medium text-gold sm:px-6 sm:py-3 sm:text-sm transition hover:bg-gold hover:text-gold-foreground">{{ $m('steps_link') }} <x-icon name="arrow-right" /></a>
                </div>
            @endif
        </div>
    </section>
    @endif

    {{-- 5. Browse by category --}}
    @if ($categories->isNotEmpty())
        <section class="border-b border-border bg-card">
            <div class="mx-auto max-w-[1200px] px-4 py-[30px] md:py-[70px] sm:px-6">
            <x-section-heading :eyebrow="$m('categories_label')" :title="$m('categories_heading')" />
            <div class="lg:mt-10 mt-6 grid gap-3 sm:grid-cols-2 grid-cols-2 lg:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('archive.index', ['category' => $category->id]) }}" class="group flex flex-col justify-between border border-border bg-background lg:p-5 p-3 transition hover:-translate-y-0.5 hover:border-gold hover:shadow-md">
                        <span class="font-display lg:text-lg text-[16px] leading-snug group-hover:text-primary">{{ $category->name }}</span>
                        <span class="mt-4 flex flex-wrap items-center justify-between text-xs text-muted-foreground">
                            <span>{{ $category->wordLimitLabel() }}</span>
                            <span class="rounded-full bg-secondary px-2 py-0.5">{{ $category->submissions_count }} {{ Str::plural('article', $category->submissions_count) }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
            </div>
        </section>
    @endif

    {{-- 6. Latest publications --}}
    <section class="bg-background">
        <div class="mx-auto max-w-[1200px] px-4 py-[30px] md:py-[70px] sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-heading :eyebrow="$m('latest_label')" :title="$m('latest_heading')" class="flex-1" />
                @if ($m('latest_link'))<a href="{{ route('archive.index') }}" class="inline-flex items-center gap-1 text-sm text-primary hover:underline">{{ $m('latest_link') }} <x-icon name="arrow-right" /></a>@endif
            </div>
            @if ($publications->isEmpty())
                <p class="mt-10 border border-dashed border-border bg-card p-8 text-center text-muted-foreground">The first articles will appear here once published.</p>
            @else
                <div x-data="{ i: 0 }" class="lg:mt-10 mt-6">
                    <div x-ref="track" @scroll.passive="i = Math.round($refs.track.scrollLeft / ($refs.track.scrollWidth / {{ $publications->count() }}))" class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] sm:mx-0 sm:grid sm:snap-none sm:grid-cols-2 sm:gap-5 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-3 [&::-webkit-scrollbar]:hidden">
                        @foreach ($publications as $submission)
                            <div class="flex w-[85%] shrink-0 snap-center flex-col sm:w-auto [&>article]:flex-1">@include('site._article-card')</div>
                        @endforeach
                    </div>
                    <div class="mt-5 flex justify-center gap-2 sm:hidden">
                        @foreach ($publications as $submission)
                            <button type="button" aria-label="Go to article {{ $loop->iteration }}" @click="$refs.track.children[{{ $loop->index }}].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' })" class="h-2 rounded-full transition-all duration-300" :class="i === {{ $loop->index }} ? 'w-6 bg-primary' : 'w-2 bg-primary/25'"></button>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- 8. From the blog --}}
    @if ($blogs->isNotEmpty())
        <section class="border-y border-border bg-secondary">
            <div class="mx-auto max-w-[1200px] px-4 py-[30px] md:py-[70px] sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-heading :eyebrow="$m('blog_label')" :title="$m('blog_heading')" class="flex-1" />
                @if ($m('blog_link'))<a href="{{ route('blogs.index') }}" class="inline-flex items-center gap-1 text-sm text-primary hover:underline">{{ $m('blog_link') }} <x-icon name="arrow-right" /></a>@endif
            </div>
            <div x-data="{ i: 0 }" class="mt-6 lg:mt-10">
            <div x-ref="track" @scroll.passive="i = Math.round($refs.track.scrollLeft / ($refs.track.scrollWidth / {{ $blogs->count() }}))" class="-mx-4 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-2 [scrollbar-width:none] sm:mx-0 sm:grid sm:snap-none sm:grid-cols-2 sm:gap-6 sm:overflow-visible sm:px-0 sm:pb-0 md:grid-cols-3 [&::-webkit-scrollbar]:hidden">
                @foreach ($blogs as $blog)
                    <a href="{{ route('blogs.show', $blog->slug) }}" class="group flex w-[85%] shrink-0 snap-center flex-col sm:w-auto overflow-hidden border border-border bg-card transition hover:-translate-y-0.5 hover:border-gold hover:shadow-md">
                        <span class="block aspect-[16/9] overflow-hidden bg-secondary">
                            @if ($blog->featured_image)
                                <img src="{{ Storage::disk('public')->url($blog->featured_image) }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <span class="flex h-full items-center justify-center bg-gradient-to-br from-secondary to-gold/20"><x-icon name="book-open" class="h-8 w-8 text-gold/70" /></span>
                            @endif
                        </span>
                        <span class="flex flex-1 flex-col p-5">
                            <span class="label-caps text-[0.65rem] text-primary">{{ $blog->category->category_name }} · {{ format_date($blog->publish_date) }}</span>
                            <span class="mt-2 line-clamp-2 font-display text-lg leading-snug group-hover:text-primary">{{ $blog->blog_title }}</span>
                            <span class="mt-2 line-clamp-3 text-sm text-muted-foreground">{{ $blog->excerpt }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
            <div class="mt-5 flex justify-center gap-2 sm:hidden">
                @foreach ($blogs as $blog)
                    <button type="button" aria-label="Go to post {{ $loop->iteration }}" @click="$refs.track.children[{{ $loop->index }}].scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' })" class="h-2 rounded-full transition-all duration-300" :class="i === {{ $loop->index }} ? 'w-6 bg-primary' : 'w-2 bg-primary/25'"></button>
                @endforeach
            </div>
            </div>
            </div>
        </section>
    @endif

    {{-- Optional content from Admin → Pages → Home --}}
    @if ($page?->content)
        <section class="border-b border-border bg-card"><div class="mx-auto max-w-[1200px] px-4 py-[30px] md:py-[70px] sm:px-6"><div class="prose-legis">{!! $page->content !!}</div></div></section>
    @endif

    {{-- 9. Call to action --}}
    @if ($m('cta_heading') || $m('cta_text') || $m('cta_primary') || $m('cta_secondary'))
    <section class="bg-foreground text-background">
        <div class="mx-auto grid max-w-[1200px] items-center gap-8 px-4 py-[30px] md:py-[70px] sm:px-6 lg:grid-cols-[1fr_auto] lg:gap-12">
            <div>
                @if ($m('cta_label'))<p class="label-caps text-sm text-gold">{{ $m('cta_label') }}</p>@endif
                @if ($m('cta_heading'))<h2 class="mt-2 font-display text-3xl">{{ $m('cta_heading') }}</h2>@endif
                @if ($m('cta_text'))<p class="measure mt-2 opacity-80">{{ $m('cta_text') }}</p>@endif
            </div>
            <div class="flex flex-wrap gap-3">
                @if ($m('cta_primary'))<a href="{{ page_url('submit') }}" class="inline-flex h-10 items-center gap-2 border border-primary bg-primary px-4 text-xs font-semibold sm:h-12 sm:px-6 sm:text-sm text-primary-foreground hover:bg-primary/90">{{ $m('cta_primary') }} <x-icon name="arrow-right" /></a>@endif
                @if ($m('cta_secondary'))<a href="{{ page_url('plagiarism_checker') }}" class="inline-flex h-10 items-center gap-2 border border-background/40 px-4 text-xs font-semibold sm:h-12 sm:px-6 sm:text-sm hover:bg-background/10"><x-icon name="scan-search" /> {{ $m('cta_secondary') }}</a>@endif
            </div>
        </div>
    </section>
    @endif
</x-layouts.site>
