<x-layouts.site :title="$page?->seo_title ?: 'Best Paper Winners'" :description="$page?->seo_description">
    <x-page-header eyebrow="Recognition" :title="$page?->title ?: 'Best Paper Winners'" :intro="$page?->excerpt ?: 'Each month the Editorial Board selects one published manuscript for recognition.'" />
    <div class="mx-auto max-w-[1200px] space-y-16 px-6 py-16">
        <section>
            <x-section-heading eyebrow="Current Winner" :title="$current ? 'Best Paper — '.$current->periodLabel() : 'This month\'s winner will be announced soon'" />
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
            @foreach (['winner_choose_desc' => 'How Winners Are Chosen', 'prize_desc' => 'The Prize', 'be_considered_desc' => 'Be Considered Next Month'] as $key => $heading)
                @if ($page?->meta($key))
                    <div class="border border-border bg-card p-6">
                        <p class="label-caps text-sm text-primary">The Award</p>
                        <h3 class="mt-2 font-display text-2xl">{{ $heading }}</h3>
                        <p class="mt-3 text-muted-foreground">{{ $page->meta($key) }}</p>
                        @if ($key === 'be_considered_desc')<x-button class="mt-5" variant="primary" icon="send" :href="route('submit')">Submit a Manuscript</x-button>@endif
                    </div>
                @endif
            @endforeach
        </section>

        <section id="past">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <x-section-heading eyebrow="Archive" title="Past Winners" class="flex-1" />
                <form method="GET" action="{{ route('best-paper') }}#past" class="flex flex-wrap items-end gap-3">
                    <x-filter.select name="year" label="Year" :options="$years->mapWithKeys(fn ($y) => [$y => $y])" all="All years" />
                    <x-filter.select name="category" label="Category" :options="$categories" all="All categories" />
                    <x-button type="submit" icon="filter">Filter</x-button>
                </form>
            </div>
            <div class="mt-8 grid gap-px bg-border md:grid-cols-2">
                @forelse ($past as $award)
                    <div class="bg-card p-6">
                        <p class="label-caps text-xs text-primary">{{ $award->periodLabel() }} · {{ ucfirst($award->period_type->value) }}</p>
                        <p class="mt-2 font-display text-xl"><a href="{{ route('archive.show', $award->submission) }}" class="hover:text-primary">{{ $award->submission->title }}</a></p>
                        <p class="text-sm text-muted-foreground">{{ $award->submission->author->name }} · {{ $award->submission->contentCategory->name }}</p>
                    </div>
                @empty
                    <p class="bg-card p-6 text-muted-foreground md:col-span-2">No past winners match these filters.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.site>
