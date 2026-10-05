<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: $page->excerpt">
    <x-page-header :title="$page->title" :intro="$page->excerpt" />
    <div class="mx-auto max-w-[1200px] space-y-12 px-4 py-8 sm:px-6 md:py-10">
        <section>
            <x-section-heading eyebrow="Current Winner" :title="$current ? 'Best Paper — '.$current->periodLabel() : 'This quarter\'s winner will be announced soon'" />
            @if ($current)
                <div class="mt-8 grid gap-8 border border-gold/60 bg-card p-8 md:grid-cols-[auto_1fr]">
                    <x-icon name="award" class="h-16 w-16 text-gold" />
                    <div>
                        <h3 class="font-display text-3xl"><a href="{{ route('archive.show', $current->submission) }}" class="hover:text-primary">{{ $current->submission->title }}</a></h3>
                        <p class="mt-2 text-muted-foreground">{{ collect([$current->submission->author->name])->merge($current->submission->co_authors ?? [])->implode(', ') }} · {{ $current->submission->contentCategory->name }}</p>
                        <blockquote class="measure mt-5 border-l-2 border-gold pl-4 text-lg italic">{{ $current->editorial_citation }}</blockquote>
                        @if ($current->hasPrize())<p class="mt-4 text-sm text-muted-foreground">Cash prize: {{ money($current->prize_amount) }}</p>@endif
                    </div>
                </div>
            @endif
        </section>

        <section id="past">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-heading eyebrow="Archive" title="Past Winners" class="flex-1" />
                {{-- Same filter bar as Blogs and Jobs: Filter, plus Reset once a filter is applied. --}}
                <x-filter-bar :action="page_url('paper_winner').'#past'" class="py-0!">
                    <x-filter.select name="year" label="Year" :options="$years->mapWithKeys(fn ($y) => [$y => $y])" all="All years" />
                    <x-filter.select name="category" label="Category" :options="$categories" all="All categories" />
                </x-filter-bar>
            </div>
            <div class="mt-8 grid gap-4 md:grid-cols-2">
                @forelse ($past as $award)
                    <div class="border border-border bg-card p-6">
                        <p class="label-caps text-xs text-primary">{{ $award->periodLabel() }}</p>
                        <p class="mt-2 font-display text-xl"><a href="{{ route('archive.show', $award->submission) }}" class="hover:text-primary">{{ $award->submission->title }}</a></p>
                        <p class="text-sm text-muted-foreground">{{ $award->submission->author->name }} · {{ $award->submission->contentCategory->name }}</p>
                    </div>
                @empty
                    <p class="border border-border bg-card p-6 text-muted-foreground md:col-span-2">No past winners match these filters.</p>
                @endforelse
            </div>
        </section>

        @if ($page->content)<div class="prose-legis">{!! $page->content !!}</div>@endif
    </div>
</x-layouts.site>
