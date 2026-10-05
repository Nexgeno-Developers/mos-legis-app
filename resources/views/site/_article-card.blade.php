{{-- Published manuscript card (Archive and home page). Expects $submission. --}}
@php
    $url = route('archive.show', $submission);
    $authors = collect([$submission->author->name])->merge($submission->co_authors ?? [])->filter();
    $awarded = $submission->relationLoaded('awards') && $submission->awards->isNotEmpty();
@endphp
<article class="group flex flex-col border border-border bg-card p-6 transition hover:-translate-y-0.5 hover:border-gold hover:shadow-md">
    <div class="flex flex-wrap items-center gap-2">
        <span class="label-caps border border-border bg-secondary/60 px-2 py-0.5 text-[0.65rem] text-primary">{{ $submission->contentCategory->name }}</span>
        @if ($awarded)
            <span class="label-caps inline-flex items-center gap-1 border border-gold/60 px-2 py-0.5 text-[0.65rem] text-gold"><x-icon name="award" class="h-3 w-3" /> Best Paper</span>
        @endif
    </div>
    <h3 class="mt-3 font-display text-xl leading-snug"><a href="{{ $url }}" class="line-clamp-3 hover:text-primary">{{ $submission->title }}</a></h3>
    <p class="mt-2 flex items-start gap-1.5 text-sm text-muted-foreground"><x-icon name="users" class="mt-0.5 h-3.5 w-3.5 shrink-0" /> <span class="line-clamp-2">{{ $authors->implode(', ') }}</span></p>
    <p class="mt-3 line-clamp-4 flex-1 text-sm leading-relaxed text-muted-foreground">{{ $submission->abstract }}</p>
    <p class="mt-4 flex items-center gap-1.5 font-mono text-xs text-muted-foreground">
        <x-icon name="calendar-range" class="h-3.5 w-3.5" /> {{ format_date($submission->published_at) }}
        @if ($submission->theme)<span aria-hidden="true">·</span> Vol. {{ $submission->theme->volume }}@endif
        <span aria-hidden="true">·</span> {{ $submission->reference() }}
    </p>
    <div class="mt-4 flex items-center justify-between gap-3 border-t border-border pt-4 text-sm">
        <a href="{{ $url }}" class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">Read abstract <x-icon name="arrow-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" /></a>
        <a href="{{ route('archive.download', $submission) }}" class="inline-flex items-center gap-1 text-muted-foreground hover:text-primary" title="Download the manuscript (.docx)"><x-icon name="download" class="h-3.5 w-3.5" /> Download</a>
    </div>
</article>
