<x-layouts.site title="Blogs" description="Legal commentary from the MOS Legis editorial board and contributors.">
    <x-page-header eyebrow="Blogs" title="Legal Commentary" intro="Shorter writing from the Editorial Board and outside contributors." />
    <div class="mx-auto max-w-[1200px] px-6 py-12">
        <x-filter-bar>
            <x-filter.search placeholder="Search title…" />
            <x-filter.select name="category" label="Category" :options="$categories" />
            <x-filter.select name="tag" label="Tag" :options="$tags" />
            <x-filter.date-range label="Published" />
        </x-filter-bar>
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($blogs as $blog)
                <article class="flex flex-col border border-border bg-card">
                    @if ($blog->featured_image)
                        <a href="{{ route('blogs.show', $blog->slug) }}"><img src="{{ Storage::disk('public')->url($blog->featured_image) }}" alt="" class="h-48 w-full object-cover"></a>
                    @endif
                    <div class="flex flex-1 flex-col p-6">
                        <p class="label-caps text-xs text-muted-foreground">
                            {{ $blog->category->category_name }} · {{ format_date($blog->publish_date) }}
                            @if ($blog->featured_post)<span class="text-gold">· Editor's pick</span>@endif
                        </p>
                        <h2 class="mt-2 font-display text-xl"><a href="{{ route('blogs.show', $blog->slug) }}" class="hover:text-primary">{{ $blog->blog_title }}</a></h2>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $blog->author_name }}</p>
                        <p class="mt-3 flex-1 text-sm">{{ $blog->excerpt }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($blog->tags as $tag)<a href="{{ route('blogs.index', ['tag' => $tag->slug]) }}" class="text-xs text-muted-foreground hover:text-primary">#{{ $tag->tag_name }}</a>@endforeach
                        </div>
                    </div>
                </article>
            @empty
                <p class="text-muted-foreground">No posts match your filters.</p>
            @endforelse
        </div>
        {{ $blogs->links() }}
    </div>
</x-layouts.site>
