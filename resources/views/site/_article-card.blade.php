{{-- Published manuscript card. Expects $submission. --}}
<article class="flex flex-col border border-border bg-card p-6 transition-colors hover:border-gold hover:bg-secondary/60">
    <span class="font-mono text-[11px] tracking-wider text-muted-foreground uppercase">
        @if ($submission->theme) Vol. {{ $submission->theme->volume }} · @endif {{ $submission->contentCategory->name }} · {{ format_date($submission->published_at) }}
    </span>
    <h3 class="mt-3 font-display text-xl leading-snug"><a href="{{ route('archive.show', $submission) }}" class="hover:text-primary">{{ $submission->title }}</a></h3>
    <p class="mt-2 text-sm text-muted-foreground">{{ collect([$submission->author->name])->merge($submission->co_authors ?? [])->implode(' · ') }}</p>
    <p class="mt-4 flex-1 text-sm leading-relaxed text-muted-foreground">{{ Str::limit($submission->abstract, 220) }}</p>
    <div class="mt-5 flex items-center gap-4 text-sm">
        <a href="{{ route('archive.show', $submission) }}" class="inline-flex items-center gap-1 font-semibold text-primary hover:underline">Read more <x-icon name="arrow-right" class="h-3.5 w-3.5" /></a>
        <a href="{{ route('archive.download', $submission) }}" class="inline-flex items-center gap-1 text-muted-foreground hover:text-primary"><x-icon name="download" class="h-3.5 w-3.5" /> Download</a>
    </div>
</article>
