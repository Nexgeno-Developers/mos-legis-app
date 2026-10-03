<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description">
    <x-page-header eyebrow="Gratitude" :title="$page->title" :intro="$page->excerpt" />
    <div class="mx-auto max-w-[1200px] px-4 py-8 sm:px-6 md:py-10">
        <x-section-heading eyebrow="With Gratitude" title="Acknowledgements" />
        <ol class="mt-8 space-y-6">
            @forelse ($entries as $entry)
                <li class="border-l-2 border-gold/60 bg-card px-6 py-5">
                    <p class="text-lg italic">{{ $entry['description'] }}</p>
                    <p class="label-caps mt-2 text-xs text-muted-foreground">{{ $entry['month_year'] }}</p>
                </li>
            @empty
                <li class="text-muted-foreground">Acknowledgements will appear here.</li>
            @endforelse
        </ol>
        @if ($page->content)<div class="prose-legis mt-12">{!! $page->content !!}</div>@endif
    </div>
</x-layouts.site>
