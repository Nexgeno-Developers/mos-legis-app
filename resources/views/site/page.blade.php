<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: $page->excerpt" :og-image="$page->og_image">
    <x-page-header :title="$page->title" :intro="$page->excerpt" />
    <article class="mx-auto max-w-[1200px] px-4 py-8 sm:px-6 md:py-10">
        @if ($page->featured_image)<img src="{{ Storage::disk('public')->url($page->featured_image) }}" alt="" class="mb-10 max-h-96 w-full object-cover">@endif
        <div class="prose-legis">{!! $page->content !!}</div>
        <p class="mt-12 text-sm text-muted-foreground">Last updated {{ format_date($page->updated_at) }}</p>
    </article>
</x-layouts.site>
