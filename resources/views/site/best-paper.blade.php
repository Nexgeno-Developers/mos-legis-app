<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: $page->excerpt">
    <x-page-header :title="$page->title" :intro="$page->excerpt" />
    <div class="mx-auto max-w-[1200px] space-y-12 px-4 py-8 sm:px-6 md:py-10">
        <section>
            <x-section-heading :eyebrow="$page->meta('current_label')" :title="$current ? 'Best Paper — '.$current->periodLabel() : 'This month\'s winner will be announced soon'" />
            @if ($current)
                <div class="mt-8 grid gap-8 border border-gold/60 bg-card p-8 md:grid-cols-[auto_1fr]">
                    <x-icon name="award" class="h-16 w-16 text-gold" />
                    <div>
                        <h3 class="font-display text-3xl"><a href="{{ route('archive.show', $current->submission) }}" class="hover:text-primary">{{ $current->submission->title }}</a></h3>
                        <p class="mt-2 text-muted-foreground">{{ collect([$current->submission->author->name])->merge($current->submission->co_authors ?? [])->implode(', ') }} · {{ $current->submission->contentCategory->name }}</p>
                        <blockquote class="measure mt-5 border-l-2 border-gold pl-4 text-lg italic">{{ $current->editorial_citation }}</blockquote>
                        <p class="mt-4 text-sm text-muted-foreground">Cash prize: {{ money($current->prize_amount) }}</p>
                    </div>
                </div>
            @endif
        </section>

        <section class="grid gap-8 md:grid-cols-3">
            {{-- Three boxes: heading + text from Admin → Pages; a box with neither is left out. --}}
            @foreach (['winner_choose', 'prize', 'be_considered'] as $box)
                @if ($page->meta($box.'_title') || $page->meta($box.'_desc'))
                    <div class="border border-border bg-card p-6">
                        @if ($page->meta('cards_label'))<p class="label-caps text-sm text-primary">{{ $page->meta('cards_label') }}</p>@endif
                        @if ($page->meta($box.'_title'))<h3 class="mt-2 font-display text-2xl">{{ $page->meta($box.'_title') }}</h3>@endif
                        @if ($page->meta($box.'_desc'))<p class="mt-3 whitespace-pre-line text-muted-foreground">{{ $page->meta($box.'_desc') }}</p>@endif
                        @if ($box === 'be_considered')<x-button class="mt-5" variant="primary" icon="send" :href="page_url('submit')">Submit a Manuscript</x-button>@endif
                    </div>
                @endif
            @endforeach
        </section>

        @if ($page->content)<div class="prose-legis measure">{!! $page->content !!}</div>@endif

        <section id="past">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-heading :eyebrow="$page->meta('past_label')" :title="$page->meta('past_heading')" class="flex-1" />
                <form method="GET" action="{{ page_url('paper_winner') }}#past" class="flex flex-wrap items-end gap-3">
                    <x-filter.select name="year" label="Year" :options="$years->mapWithKeys(fn ($y) => [$y => $y])" all="All years" />
                    <x-filter.select name="category" label="Category" :options="$categories" all="All categories" />
                    <x-button type="submit" icon="filter">Filter</x-button>
                </form>
            </div>
            <div class="mt-8 grid gap-4 md:grid-cols-2">
                @forelse ($past as $award)
                    <div class="border border-border bg-card p-6">
                        <p class="label-caps text-xs text-primary">{{ $award->periodLabel() }} · {{ ucfirst($award->period_type->value) }}</p>
                        <p class="mt-2 font-display text-xl"><a href="{{ route('archive.show', $award->submission) }}" class="hover:text-primary">{{ $award->submission->title }}</a></p>
                        <p class="text-sm text-muted-foreground">{{ $award->submission->author->name }} · {{ $award->submission->contentCategory->name }}</p>
                    </div>
                @empty
                    <p class="border border-border bg-card p-6 text-muted-foreground md:col-span-2">No past winners match these filters.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.site>
