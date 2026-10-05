<x-layouts.site title="Blogs" description="Legal commentary from the MOS Legis editorial board and contributors.">
    <x-page-header eyebrow="Blogs" title="Legal Commentary" intro="Shorter writing from the Editorial Board and outside contributors." />
    <div class="mx-auto max-w-[1200px] px-4 pt-2 pb-12 sm:px-6">
        <x-filter-bar>
            <x-filter.search placeholder="Search title…" />
            <x-filter.select name="category" label="Category" :options="$categories" />
            <x-filter.select name="tag" label="Tag" :options="$tags" />
            <x-filter.date-range label="Published" />
        </x-filter-bar>

        @if ($blogs->total())
            <p class="mb-5 text-sm text-muted-foreground">{{ $blogs->total() }} {{ Str::plural('post', $blogs->total()) }}</p>
        @endif

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse ($blogs as $blog)
                @php $url = route('blogs.show', $blog->slug); @endphp
                <article class="group flex flex-col overflow-hidden border border-border bg-card transition hover:-translate-y-0.5 hover:border-gold hover:shadow-lg">
                    <a href="{{ $url }}" class="relative block aspect-[16/9] overflow-hidden bg-secondary" tabindex="-1" aria-hidden="true">
                        @if ($blog->featured_image)
                            <img src="{{ Storage::disk('public')->url($blog->featured_image) }}" alt="" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @else
                            <span class="flex h-full w-full items-center justify-center bg-gradient-to-br from-secondary to-gold/20">
                                <x-icon name="book-open" class="h-10 w-10 text-gold/70" />
                            </span>
                        @endif
                        <span class="label-caps absolute left-3 top-3 bg-card/95 px-2.5 py-1 text-[0.65rem] text-primary">{{ $blog->category->category_name }}</span>
                        @if ($blog->featured_post)
                            <span class="label-caps absolute right-3 top-3 flex items-center gap-1 bg-gold px-2.5 py-1 text-[0.65rem] text-gold-foreground"><x-icon name="star" class="h-3 w-3" /> Editor's pick</span>
                        @endif
                    </a>

                    <div class="flex flex-1 flex-col p-5">
                        <p class="flex items-center gap-2 text-xs text-muted-foreground">
                            <x-icon name="calendar-range" class="h-3.5 w-3.5" /> {{ format_date($blog->publish_date) }}
                            <span aria-hidden="true">·</span>
                            <x-icon name="clock" class="h-3.5 w-3.5" /> {{ $blog->readingMinutes() }} min read
                        </p>
                        <h2 class="mt-2 font-display text-xl leading-snug">
                            <a href="{{ $url }}" class="line-clamp-2 hover:text-primary">{{ $blog->blog_title }}</a>
                        </h2>
                        <p class="mt-2 line-clamp-3 flex-1 text-sm leading-relaxed text-muted-foreground">{{ $blog->excerpt }}</p>

                        @if ($blog->tags->isNotEmpty())
                            <div class="mt-4 flex flex-wrap gap-1.5">
                                @foreach ($blog->tags->take(3) as $tag)
                                    <a href="{{ route('blogs.index', ['tag' => $tag->slug]) }}" class="rounded-full border border-border px-2.5 py-0.5 text-xs text-muted-foreground hover:border-gold hover:text-primary">#{{ $tag->tag_name }}</a>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-5 flex items-center justify-between gap-3 border-t border-border pt-4">
                            <span class="flex min-w-0 items-center gap-2 text-sm">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-primary text-xs font-semibold text-primary-foreground">{{ Str::upper(Str::substr($blog->author_name, 0, 1)) }}</span>
                                <span class="truncate">{{ $blog->author_name }}</span>
                            </span>
                            <a href="{{ $url }}" class="label-caps flex shrink-0 items-center gap-1 text-xs text-primary">Read <x-icon name="arrow-right" class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" /></a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full border border-dashed border-border bg-card px-6 py-14 text-center">
                    <x-icon name="search" class="mx-auto h-8 w-8 text-muted-foreground" />
                    <p class="mt-3 font-display text-lg">No posts match your filters</p>
                    <p class="mt-1 text-sm text-muted-foreground">Try a different search or <a href="{{ route('blogs.index') }}" class="text-primary hover:underline">clear the filters</a>.</p>
                </div>
            @endforelse
        </div>
        <div class="mt-10">{{ $blogs->links() }}</div>
    </div>
</x-layouts.site>
