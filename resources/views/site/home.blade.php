@php
    $slides = [
        ['eyebrow' => 'Peer-Reviewed Legal Scholarship', 'headline' => 'Rooted in Tradition. Driven by Justice.', 'sub' => $page?->excerpt, 'primary' => ['Submit a Manuscript', page_url('submit')], 'secondary' => ['Browse the Archive', route('archive.index')]],
        ['eyebrow' => 'Best Paper — Monthly Winner', 'headline' => "Recognising the Month's Most Rigorous Scholarship.", 'sub' => 'Each month one paper is elevated for its research depth, originality, and clarity of argument.', 'primary' => ['See the Winner', page_url('paper_winner')], 'secondary' => ['Past Winners', page_url('paper_winner').'#past'], ],
        ['eyebrow' => 'Plagiarism Screening', 'headline' => 'Every Manuscript Screened Before Peer Review.', 'sub' => 'Check your own draft for similarity before you submit, with a downloadable report.', 'primary' => ['Try the Plagiarism Checker', page_url('plagiarism_checker')], 'secondary' => ['Submit a Manuscript', page_url('submit')]],
    ];
@endphp
<x-layouts.site :title="null" :description="$page?->seo_description">
    <section class="border-b border-border bg-secondary" x-data="{ i: 0, n: {{ count($slides) }} }" x-init="setInterval(() => i = (i + 1) % n, 8000)">
        <div class="mx-auto grid max-w-[1200px] items-center gap-12 px-6 py-20 md:grid-cols-[1fr_1.4fr] md:py-28">
            <div class="hidden justify-start md:flex">
                <img src="{{ asset('images/logo-mark.png') }}" alt="{{ settings('general.application_name') }} emblem" class="w-full max-w-[24rem] object-contain">
            </div>
            <div>
                {{-- All slides share one grid cell, so they cross-fade in place without the page jumping. --}}
                <div class="grid">
                @foreach ($slides as $index => $slide)
                    <div class="col-start-1 row-start-1" x-show="i === {{ $index }}" @if ($index) x-cloak @endif
                        x-transition:enter="transition-opacity duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                        x-transition:leave="transition-opacity duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                        <p class="label-caps text-sm text-primary">{{ $slide['eyebrow'] }}</p>
                        <h1 class="mt-4 font-display text-4xl leading-[1.1] md:text-5xl">{{ $slide['headline'] }}</h1>
                        <div class="gold-rule my-6 max-w-md"></div>
                        @if ($slide['sub'])<p class="measure text-lg text-muted-foreground">{{ $slide['sub'] }}</p>@endif
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ $slide['primary'][1] }}" class="inline-flex h-12 items-center gap-2 border border-primary bg-primary px-6 text-sm font-semibold text-primary-foreground hover:bg-primary/90">{{ $slide['primary'][0] }} <x-icon name="arrow-right" /></a>
                            <a href="{{ $slide['secondary'][1] }}" class="inline-flex h-12 items-center gap-2 border border-gold px-6 text-sm font-semibold hover:bg-gold/10">{{ $slide['secondary'][0] }}</a>
                        </div>
                    </div>
                @endforeach
                </div>
                <div class="mt-10 flex items-center gap-3">
                    <button type="button" @click="i = (i - 1 + n) % n" aria-label="Previous slide" class="inline-flex h-9 w-9 items-center justify-center border border-border text-muted-foreground hover:border-gold"><x-icon name="chevron-left" /></button>
                    <button type="button" @click="i = (i + 1) % n" aria-label="Next slide" class="inline-flex h-9 w-9 items-center justify-center border border-border text-muted-foreground hover:border-gold"><x-icon name="chevron-right" /></button>
                    <span class="font-mono text-xs text-muted-foreground" x-text="String(i + 1).padStart(2, '0') + ' / ' + String(n).padStart(2, '0')"></span>
                </div>
            </div>
        </div>
    </section>

    <section class="border-b border-border">
        <div class="mx-auto flex max-w-[1200px] flex-wrap gap-2 px-6 py-6">
            @foreach ($categories as $category)
                <a href="{{ route('archive.index', ['category' => $category->id]) }}" class="rounded-full border border-border bg-muted px-3 py-1 text-xs text-muted-foreground hover:border-gold hover:text-foreground">{{ $category->name }}</a>
            @endforeach
        </div>
    </section>

    <section class="mx-auto max-w-[1200px] px-6 py-20">
        <div class="flex items-end justify-between gap-6">
            <x-section-heading eyebrow="Latest Publications" title="Recently Published Scholarship" class="flex-1" />
            <a href="{{ route('archive.index') }}" class="hidden shrink-0 items-center gap-1 text-sm text-primary hover:underline sm:inline-flex">View full archive <x-icon name="arrow-right" /></a>
        </div>
        @if ($publications->isEmpty())
            <p class="mt-10 text-muted-foreground">The first articles will appear here once published.</p>
        @else
            <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($publications as $submission)@include('site._article-card')@endforeach
            </div>
        @endif
    </section>

    @if ($winner)
        <section class="border-y border-border bg-card">
            <div class="mx-auto grid max-w-[1200px] gap-10 px-6 py-16 md:grid-cols-[auto_1fr] md:items-center">
                <x-icon name="award" class="h-20 w-20 text-gold" />
                <div>
                    <p class="label-caps text-sm text-primary">Best Paper · {{ $winner->periodLabel() }}</p>
                    <h2 class="mt-2 font-display text-3xl"><a href="{{ route('archive.show', $winner->submission) }}" class="hover:text-primary">{{ $winner->submission->title }}</a></h2>
                    <p class="mt-2 text-muted-foreground">{{ $winner->submission->author->name }} · {{ $winner->submission->contentCategory->name }}</p>
                    <p class="measure mt-4 italic">“{{ $winner->editorial_citation }}”</p>
                </div>
            </div>
        </section>
    @endif

    @if ($page?->content)
        <section class="mx-auto max-w-[1200px] px-6 py-16"><div class="prose-legis measure">{!! $page->content !!}</div></section>
    @endif

    @if ($blogs->isNotEmpty())
        <section class="mx-auto max-w-[1200px] px-6 pb-20">
            <x-section-heading eyebrow="From the Blog" title="Legal Commentary" />
            <div class="mt-10 grid gap-8 md:grid-cols-3">
                @foreach ($blogs as $blog)
                    <article>
                        <p class="label-caps text-xs text-muted-foreground">{{ $blog->category->category_name }} · {{ format_date($blog->publish_date) }}</p>
                        <h3 class="mt-2 font-display text-xl"><a href="{{ route('blogs.show', $blog->slug) }}" class="hover:text-primary">{{ $blog->blog_title }}</a></h3>
                        <p class="mt-2 text-sm text-muted-foreground">{{ $blog->excerpt }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <section class="border-y border-border bg-secondary">
        <div class="mx-auto flex max-w-[1200px] flex-col items-center gap-4 px-6 py-16 text-center">
            <p class="label-caps text-sm text-primary">Payment Timing</p>
            <h2 class="font-display text-3xl">No fee until you&rsquo;re accepted.</h2>
            <p class="measure text-muted-foreground">Only the plagiarism pre-screening fee is due upfront. The publication fee is payable after an editorial acceptance decision.</p>
            <a href="{{ page_url('submit') }}" class="mt-2 inline-flex h-12 items-center gap-2 border border-primary bg-primary px-6 text-sm font-semibold text-primary-foreground hover:bg-primary/90">Start a Submission <x-icon name="arrow-right" /></a>
        </div>
    </section>
</x-layouts.site>
