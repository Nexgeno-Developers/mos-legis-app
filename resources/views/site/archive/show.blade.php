@php
    $authors = collect([$submission->author->name])->merge($submission->co_authors ?? []);
    $citation = $authors->implode(', ').', “'.$submission->title.'”, '.settings('general.application_name')
        .($submission->theme ? ', Vol. '.$submission->theme->volume : '').' ('.$submission->published_at->format('Y').').';
@endphp
<x-layouts.site :title="$submission->title" :description="Str::limit($submission->abstract, 160)">
    <x-page-header :crumbs="['Archive' => route('archive.index'), $submission->contentCategory->name => route('archive.index', ['category' => $submission->content_category_id])]" :title="$submission->title" :intro="$authors->implode(' · ')" />
    <div class="mx-auto grid max-w-[1200px] gap-12 px-4 py-8 sm:px-6 md:py-10 lg:grid-cols-[1fr_20rem]">
        <article>
            @foreach ($submission->awards as $award)<x-badge tone="gold" class="mb-4">Best Paper · {{ $award->periodLabel() }}</x-badge>@endforeach
            <h2 class="font-display text-2xl">Abstract</h2>
            <p class="measure mt-3 whitespace-pre-line text-lg leading-relaxed">{{ $submission->abstract }}</p>
            <h3 class="label-caps mt-10 text-sm text-primary">Keywords</h3>
            <div class="mt-2 flex flex-wrap gap-2">
                @foreach ($submission->keywords ?? [] as $keyword)
                    <a href="{{ route('archive.index', ['keyword' => $keyword]) }}" class="rounded-full border border-border px-3 py-1 text-xs hover:border-gold">{{ $keyword }}</a>
                @endforeach
            </div>
            <div class="mt-10 border border-border bg-card p-6" x-data="{ copied: false }">
                <p class="label-caps text-xs text-muted-foreground">Recommended citation</p>
                <p class="mt-2 font-mono text-sm" x-ref="citation">{{ $citation }}</p>
                <button type="button" class="mt-3 text-sm text-primary hover:underline" @click="navigator.clipboard.writeText($refs.citation.innerText); copied = true" x-text="copied ? 'Copied' : 'Copy citation'"></button>
            </div>
        </article>
        <aside class="space-y-6">
            <x-button variant="primary" icon="download" class="w-full" :href="route('archive.download', $submission)">Download manuscript</x-button>
            <x-dl class="sm:grid-cols-1!" :items="[
                'Manuscript ID' => e($submission->reference()),
                'Published' => format_date($submission->published_at),
                'Category' => e($submission->contentCategory->name),
                'Volume & theme' => e($submission->theme?->fullLabel()),
                'Institution' => e($submission->institution),
                'ORCID' => e($submission->author->authorProfile?->orcid),
            ]" />
            @if ($related->isNotEmpty())
                <div>
                    <p class="label-caps text-xs text-muted-foreground">More in {{ $submission->contentCategory->name }}</p>
                    <ul class="mt-2 space-y-3">
                        @foreach ($related as $item)
                            <li><a href="{{ route('archive.show', $item) }}" class="hover:text-primary">{{ $item->title }}</a><br><span class="text-sm text-muted-foreground">{{ $item->author->name }}</span></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </div>
</x-layouts.site>
