@php
    $authors = collect([$submission->author->name])->merge($submission->co_authors ?? [])->filter()->values();
    $citation = $authors->implode(', ').', “'.$submission->title.'”, '.settings('general.application_name')
        .($submission->theme ? ', Vol. '.$submission->theme->volume : '').' ('.$submission->published_at->format('Y').').';
    $orcid = $submission->author->authorProfile?->orcid;
    $awards = $submission->awards->sortByDesc(fn ($a) => $a->periodOrder())->values();
    $shareUrl = urlencode(url()->current());
    $categoryUrl = route('archive.index', ['category' => $submission->content_category_id]);

    // Only the details that exist (no empty "—" rows).
    $details = array_filter([
        ['hash', 'Manuscript ID', e($submission->reference())],
        ['calendar-range', 'Published', format_date($submission->published_at)],
        ['book-open', 'Category', '<a href="'.e($categoryUrl).'" class="text-primary hover:underline">'.e($submission->contentCategory->name).'</a>'],
        $submission->theme ? ['tag', 'Volume & theme', e($submission->theme->fullLabel())] : null,
        $submission->word_count ? ['file-text', 'Length', number_format($submission->word_count).' words'] : null,
        $submission->institution ? ['home', 'Institution', e($submission->institution)] : null,
        $orcid ? ['badge-check', 'Author ORCID iD', '<a href="https://orcid.org/'.e($orcid).'" target="_blank" rel="noopener" class="text-primary hover:underline">'.e($orcid).'</a>'] : null,
    ]);
@endphp
<x-layouts.site :title="$submission->title" :description="Str::limit($submission->abstract, 160)">
    <x-page-header :crumbs="['Archive' => route('archive.index'), $submission->contentCategory->name => $categoryUrl]" :title="$submission->title">
        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-muted-foreground">
            <span class="flex items-center gap-2 text-foreground">
                <x-icon name="users" class="h-4 w-4 text-primary" />
                <span>{{ $authors->implode(', ') }}</span>
            </span>
            <span class="flex items-center gap-1.5"><x-icon name="calendar-range" class="h-4 w-4" /> Published {{ format_date($submission->published_at) }}</span>
            <a href="{{ $categoryUrl }}" class="flex items-center gap-1.5 hover:text-primary"><x-icon name="book-open" class="h-4 w-4" /> {{ $submission->contentCategory->name }}</a>
            <span class="flex items-center gap-1.5 font-mono text-xs"><x-icon name="hash" class="h-4 w-4" /> {{ $submission->reference() }}</span>
        </div>
        @if ($awards->isNotEmpty())
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($awards as $award)
                    <span class="label-caps inline-flex items-center gap-1.5 border border-gold/60 bg-card px-2.5 py-1 text-[0.65rem] text-gold"><x-icon name="award" class="h-3.5 w-3.5" /> Best Paper · {{ $award->periodLabel() }}</span>
                @endforeach
            </div>
        @endif
    </x-page-header>

    <div class="mx-auto grid max-w-[1200px] gap-10 px-4 py-[30px] md:py-[70px] sm:px-6 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <article class="min-w-0 space-y-8">
            {{-- Best Paper recognition --}}
            @if ($awards->isNotEmpty())
                <section class="flex gap-4 border border-gold/60 bg-card p-5 md:p-6">
                    <x-icon name="award" class="h-10 w-10 shrink-0 text-gold" />
                    <div class="min-w-0">
                        <p class="label-caps text-xs text-gold">Best Paper Award</p>
                        <p class="mt-1 font-display text-xl">Selected as Best Paper for {{ $awards->map->periodLabel()->implode(', ') }}</p>
                        @if ($awards->first()->editorial_citation)
                            <blockquote class="mt-3 border-l-2 border-gold pl-4 italic text-muted-foreground">{{ $awards->first()->editorial_citation }}</blockquote>
                        @endif
                    </div>
                </section>
            @endif

            <section class="border border-border bg-card p-6 md:p-8">
                <h2 class="flex items-center gap-2 font-display text-2xl"><x-icon name="file-text" class="h-5 w-5 text-primary" /> Abstract</h2>
                <p class="mt-4 whitespace-pre-line text-lg leading-relaxed">{{ $submission->abstract }}</p>

                @if (! empty($submission->keywords))
                    <div class="mt-8 border-t border-border pt-6">
                        <h3 class="label-caps text-xs text-muted-foreground">Keywords</h3>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($submission->keywords as $keyword)
                                <a href="{{ route('archive.index', ['keyword' => $keyword]) }}" title="Find more manuscripts with this keyword"
                                    class="inline-flex items-center gap-1 rounded-full border border-border bg-background px-3 py-1 text-sm hover:border-gold hover:text-primary"><x-icon name="tag" class="h-3 w-3" /> {{ $keyword }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            {{-- How to cite --}}
            <section class="border border-border bg-card p-6" x-data="{ copied: false }">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="flex items-center gap-2 font-display text-xl"><x-icon name="copy" class="h-5 w-5 text-primary" /> How to cite this manuscript</h2>
                    <button type="button" class="inline-flex items-center gap-1.5 border border-border px-3 py-1.5 text-sm hover:border-gold"
                        @click="navigator.clipboard.writeText($refs.citation.innerText); copied = true; setTimeout(() => copied = false, 2500)">
                        <span x-show="!copied" class="inline-flex items-center gap-1.5"><x-icon name="copy" class="h-4 w-4" /> Copy citation</span>
                        <span x-show="copied" x-cloak class="inline-flex items-center gap-1.5 text-success"><x-icon name="check" class="h-4 w-4" /> Copied</span>
                    </button>
                </div>
                <p class="mt-4 border-l-2 border-gold/60 bg-secondary/60 px-4 py-3 text-sm leading-relaxed" x-ref="citation">{{ $citation }}</p>
            </section>
        </article>

        <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
            <div class="border border-border bg-card p-5">
                <x-button variant="primary" icon="download" class="w-full" :href="route('archive.download', $submission)">Download manuscript</x-button>
                <p class="mt-2 text-center text-xs text-muted-foreground">Word document (.docx){{ $submission->word_count ? ' · '.number_format($submission->word_count).' words' : '' }}</p>

                <div class="mt-5 flex items-center justify-center gap-2 border-t border-border pt-4 text-sm" x-data="{ copied: false }">
                    <span class="mr-1 text-muted-foreground">Share</span>
                    <a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" class="grid h-9 w-9 place-items-center border border-border hover:border-gold hover:text-primary" aria-label="Share on LinkedIn"><x-social-icon network="linkedin" /></a>
                    <a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ urlencode($submission->title) }}" class="grid h-9 w-9 place-items-center border border-border hover:border-gold hover:text-primary" aria-label="Share on X"><x-social-icon network="twitter" /></a>
                    <button type="button" class="grid h-9 w-9 place-items-center border border-border hover:border-gold hover:text-primary" aria-label="Copy link"
                        @click="navigator.clipboard.writeText(@js(url()->current())); copied = true; setTimeout(() => copied = false, 2000)">
                        <span x-show="!copied"><x-icon name="link" /></span><span x-show="copied" x-cloak><x-icon name="check" class="text-success" /></span>
                    </button>
                </div>
            </div>

            <div class="border border-border bg-card p-5">
                <p class="label-caps text-xs text-muted-foreground">About this manuscript</p>
                <dl class="mt-4 space-y-4 text-sm">
                    @foreach ($details as [$icon, $label, $value])
                        <div class="flex gap-3">
                            <x-icon :name="$icon" class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                            <div class="min-w-0">
                                <dt class="text-xs text-muted-foreground">{{ $label }}</dt>
                                <dd class="mt-0.5 break-words font-medium">{!! $value !!}</dd>
                            </div>
                        </div>
                    @endforeach
                </dl>
            </div>
        </aside>
    </div>

    {{-- More in this category --}}
    @if ($related->isNotEmpty())
        <section class="border-t border-border bg-secondary/40">
            <div class="mx-auto max-w-[1200px] px-4 py-[30px] md:py-[70px] sm:px-6">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <h2 class="font-display text-2xl">More in {{ $submission->contentCategory->name }}</h2>
                    <a href="{{ $categoryUrl }}" class="label-caps inline-flex items-center gap-1 text-xs text-primary hover:underline">View all <x-icon name="arrow-right" class="h-3.5 w-3.5" /></a>
                </div>
                <div class="mt-6 grid gap-4 md:grid-cols-3">
                    @foreach ($related as $item)
                        <a href="{{ route('archive.show', $item) }}" class="group flex flex-col border border-border bg-card p-5 transition hover:-translate-y-0.5 hover:border-gold hover:shadow-md">
                            <span class="font-mono text-xs text-muted-foreground">{{ $item->reference() }} · {{ format_date($item->published_at) }}</span>
                            <span class="mt-2 line-clamp-3 font-display text-lg group-hover:text-primary">{{ $item->title }}</span>
                            <span class="mt-auto pt-3 text-sm text-muted-foreground">{{ $item->author->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.site>
