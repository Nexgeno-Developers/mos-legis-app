@php
    $active = collect([
        'category' => $categories->firstWhere('id', (int) request('category'))?->name,
        'q' => request('q') ? 'Search: '.request('q') : null,
        'title' => request('title') ? 'Title: '.request('title') : null,
        'author' => request('author') ? 'Author: '.request('author') : null,
        'keyword' => request('keyword') ? 'Keyword: '.request('keyword') : null,
        'year' => request('year') ? 'Year: '.request('year') : null,
    ])->filter();
    $sorts = ['newest' => 'Newest first', 'oldest' => 'Oldest first', 'title' => 'Title A–Z'];
    $sort = array_key_exists(request('sort'), $sorts) ? request('sort') : 'newest';
    $from = $submissions->firstItem();
    $to = $submissions->lastItem();
@endphp
<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: $page->excerpt" :og-image="$page->og_image">
    <x-page-header :title="$page->title" :intro="$page->excerpt" />

    <div class="mx-auto grid max-w-[1200px] gap-8 px-4 py-[30px] md:py-[70px] sm:px-6 lg:grid-cols-[18rem_minmax(0,1fr)]" x-data="{ filters: false }">
        {{-- Sidebar filters (collapsible on phones) --}}
        <aside class="lg:sticky lg:top-28 lg:self-start">
            <button type="button" class="flex w-full items-center justify-between border border-border bg-card px-4 py-3 text-sm font-semibold lg:hidden" @click="filters = !filters" :aria-expanded="filters">
                <span class="flex items-center gap-2"><x-icon name="filter" class="h-4 w-4" /> Filters @if ($active->isNotEmpty())<span class="rounded-full bg-primary px-2 text-xs text-primary-foreground">{{ $active->count() }}</span>@endif</span>
                <span class="transition-transform" :class="filters && 'rotate-180'"><x-icon name="chevron-down" class="h-4 w-4" /></span>
            </button>

            <form method="GET" action="{{ route('archive.index') }}" class="mt-3 hidden border border-border bg-card lg:mt-0 lg:block" :class="filters && 'block!'">
                @if ($sort !== 'newest')<input type="hidden" name="sort" value="{{ $sort }}">@endif

                <div class="flex items-center justify-between border-b border-border px-5 py-4">
                    <p class="font-display text-lg">Filter articles</p>
                    @if ($active->isNotEmpty())<a href="{{ route('archive.index') }}" class="text-sm text-primary hover:underline">Clear all</a>@endif
                </div>

                {{-- Search --}}
                <div class="space-y-2 border-b border-border px-5 py-5">
                    <p class="label-caps text-xs text-muted-foreground">Search</p>
                    <label class="block">
                        <span class="sr-only">Search the archive</span>
                        <span class="relative block">
                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground"><x-icon name="search" class="h-4 w-4" /></span>
                            <input type="search" name="q" value="{{ request('q') }}" placeholder="Title, author or keyword" class="field-input h-11 pl-9!" aria-label="Search by title, author or keyword">
                        </span>
                    </label>
                    <p class="text-xs text-muted-foreground">Searches titles, authors, co-authors and keywords.</p>
                    {{-- Keep a keyword/title/author filter that came from a link (e.g. a keyword tag). --}}
                    @foreach (['title', 'author', 'keyword'] as $field)
                        @if (request($field))<input type="hidden" name="{{ $field }}" value="{{ request($field) }}">@endif
                    @endforeach
                </div>

                {{-- Category --}}
                <div class="border-b border-border px-5 py-5">
                    <p class="label-caps text-xs text-muted-foreground">Content category</p>
                    <div class="mt-3 space-y-1">
                        @foreach (collect([['id' => '', 'name' => 'All categories', 'count' => $categories->sum('submissions_count')]])->merge($categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'count' => $c->submissions_count])) as $option)
                            @php $checked = (string) request('category', '') === (string) $option['id']; @endphp
                            <label @class(['flex cursor-pointer items-center gap-3 px-2 py-1.5 text-sm transition-colors hover:bg-secondary', 'bg-secondary font-semibold text-primary' => $checked, 'text-muted-foreground' => ! $checked && $option['count'] === 0])>
                                <input type="radio" name="category" value="{{ $option['id'] }}" @checked($checked) class="accent-[var(--color-primary)]" onchange="this.form.submit()">
                                <span class="flex-1">{{ $option['name'] }}</span>
                                <span class="rounded-full bg-background px-2 text-xs text-muted-foreground">{{ $option['count'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Year --}}
                <div class="px-5 py-5">
                    <label for="year" class="label-caps text-xs text-muted-foreground">Year published</label>
                    <select id="year" name="year" data-native class="field-input mt-2 h-11">
                        <option value="">All years</option>
                        @foreach ($years as $year)<option value="{{ $year }}" @selected((string) request('year') === (string) $year)>{{ $year }}</option>@endforeach
                    </select>
                    <x-button type="submit" variant="primary" icon="search" class="mt-5 w-full">Apply filters</x-button>
                </div>
            </form>
        </aside>

        {{-- Results --}}
        <section class="min-w-0">
            <div class="flex flex-wrap items-center justify-between gap-4 border border-border bg-card px-5 py-4">
                <p class="text-sm text-muted-foreground">
                    @if ($submissions->total())
                        Showing <strong class="text-foreground">{{ $from }}–{{ $to }}</strong> of <strong class="text-foreground">{{ $submissions->total() }}</strong> {{ Str::plural('article', $submissions->total()) }}
                    @else
                        <strong class="text-foreground">0</strong> articles
                    @endif
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <form method="GET" action="{{ route('archive.index') }}" class="flex items-center gap-2 text-sm">
                        @foreach (request()->except(['sort', 'page']) as $key => $value)
                            @if (is_string($value) && $value !== '')<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                        @endforeach
                        <label for="sort" class="text-muted-foreground">Sort by</label>
                        <select id="sort" name="sort" data-native class="field-input h-10 w-40 py-0" onchange="this.form.submit()">
                            @foreach ($sorts as $value => $label)<option value="{{ $value }}" @selected($sort === $value)>{{ $label }}</option>@endforeach
                        </select>
                    </form>
                    @if ($submissions->total())
                        <x-button :href="route('archive.zip', request()->except('page'))" size="sm" icon="archive" title="Download every manuscript in these results">Download all (ZIP)</x-button>
                    @endif
                </div>
            </div>

            @if ($active->isNotEmpty())
                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-muted-foreground">Active filters:</span>
                    @foreach ($active as $key => $label)
                        <a href="{{ request()->fullUrlWithQuery([$key => null, 'page' => null]) }}" class="inline-flex items-center gap-1 rounded-full border border-gold/60 bg-card px-3 py-1 hover:border-primary hover:text-primary" title="Remove this filter">
                            {{ $label }} <x-icon name="x" class="h-3 w-3" />
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="mt-5 space-y-4">
                @forelse ($submissions as $submission)
                    @php
                        $url = route('archive.show', $submission);
                        $authors = collect([$submission->author->name])->merge($submission->co_authors ?? [])->filter();
                    @endphp
                    <article class="group border border-border bg-card p-5 transition hover:border-gold hover:shadow-md md:p-6">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <a href="{{ request()->fullUrlWithQuery(['category' => $submission->content_category_id, 'page' => null]) }}" class="label-caps border border-border bg-secondary/60 px-2 py-0.5 text-[0.65rem] text-primary hover:border-primary">{{ $submission->contentCategory->name }}</a>
                            @if ($submission->awards->isNotEmpty())
                                <span class="label-caps inline-flex items-center gap-1 border border-gold/60 px-2 py-0.5 text-[0.65rem] text-gold"><x-icon name="award" class="h-3 w-3" /> Best Paper</span>
                            @endif
                            <span class="ml-auto font-mono text-muted-foreground">{{ $submission->reference() }}</span>
                        </div>
                        <h2 class="mt-3 font-display text-xl leading-snug md:text-2xl"><a href="{{ $url }}" class="hover:text-primary">{{ $submission->title }}</a></h2>
                        <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground">
                            <span class="flex items-center gap-1.5"><x-icon name="users" class="h-3.5 w-3.5" /> {{ $authors->implode(', ') }}</span>
                            <span class="flex items-center gap-1.5"><x-icon name="calendar-range" class="h-3.5 w-3.5" /> {{ format_date($submission->published_at) }}</span>
                            @if ($submission->theme)<span class="flex items-center gap-1.5"><x-icon name="book-open" class="h-3.5 w-3.5" /> Vol. {{ $submission->theme->volume }}</span>@endif
                        </p>
                        <p class="mt-3 line-clamp-3 text-sm leading-relaxed">{{ $submission->abstract }}</p>
                        <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-border pt-4">
                            <x-button :href="$url" size="sm" variant="primary" icon="arrow-right">Read abstract</x-button>
                            <x-button :href="route('archive.download', $submission)" size="sm" icon="download" title="Download the manuscript (.docx)">Download</x-button>
                            @if (! empty($submission->keywords))
                                <span class="ml-auto hidden flex-wrap gap-1.5 md:flex">
                                    @foreach (array_slice($submission->keywords, 0, 3) as $keyword)
                                        <a href="{{ request()->fullUrlWithQuery(['keyword' => $keyword, 'page' => null]) }}" class="rounded-full border border-border px-2 py-0.5 text-xs text-muted-foreground hover:border-gold hover:text-primary">#{{ $keyword }}</a>
                                    @endforeach
                                </span>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="border border-dashed border-border bg-card px-6 py-14 text-center">
                        <x-icon name="search" class="mx-auto h-8 w-8 text-muted-foreground" />
                        <p class="mt-3 font-display text-lg">No published manuscripts match your filters</p>
                        <p class="mt-1 text-sm text-muted-foreground">Try fewer words or another category, or <a href="{{ route('archive.index') }}" class="text-primary hover:underline">clear all filters</a>.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-8">{{ $submissions->links() }}</div>

            @if ($page->content)<div class="prose-legis mt-10">{!! $page->content !!}</div>@endif
        </section>
    </div>
</x-layouts.site>
