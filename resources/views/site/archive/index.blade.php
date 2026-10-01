<x-layouts.site title="Journal Archive" description="Every published MOS Legis manuscript, searchable by category, title, author and keyword.">
    <x-page-header eyebrow="Archive" title="Journal Archive" intro="Every published manuscript, filterable by content category and searchable by title, author and keyword." />
    <div class="mx-auto grid max-w-[1200px] gap-10 px-4 py-8 sm:px-6 md:py-10 lg:grid-cols-[16rem_1fr]">
        <aside>
            <form method="GET" class="space-y-6">
                <div>
                    <p class="label-caps text-xs text-muted-foreground">Content category</p>
                    <ul class="mt-2 space-y-1 text-sm">
                        <li><a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" @class(['hover:text-primary', 'font-semibold text-primary' => ! request('category')])>All categories</a></li>
                        @foreach ($categories as $category)
                            <li><a href="{{ request()->fullUrlWithQuery(['category' => $category->id, 'page' => null]) }}" @class(['flex justify-between hover:text-primary', 'font-semibold text-primary' => request('category') == $category->id])>
                                <span>{{ $category->name }}</span><span class="text-muted-foreground">{{ $category->submissions_count }}</span></a></li>
                        @endforeach
                    </ul>
                    @if (request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
                </div>
                <x-form.input name="title" label="Title" :value="request('title')" />
                <x-form.input name="author" label="Author" :value="request('author')" />
                <x-form.input name="keyword" label="Keyword" :value="request('keyword')" />
                @if ($years->isNotEmpty())
                    <x-form.select name="year" label="Year" :options="$years->mapWithKeys(fn ($y) => [$y => $y])" :value="request('year')" placeholder="All years" />
                @endif
                <div class="flex gap-2">
                    <x-button type="submit" variant="primary" icon="search">Search</x-button>
                    @if (request()->query())<x-button :href="route('archive.index')" variant="ghost">Reset</x-button>@endif
                </div>
            </form>
        </aside>
        <div>
            <div class="flex flex-wrap items-center justify-between gap-4">
                <x-section-heading eyebrow="Results" :title="$submissions->total().' '.Str::plural('article', $submissions->total())" class="flex-1" />
                @if ($submissions->total())
                    <x-button :href="route('archive.zip', request()->query())" icon="archive">Download all as ZIP</x-button>
                @endif
            </div>
            <div class="mt-8 grid gap-px bg-border md:grid-cols-2">
                @forelse ($submissions as $submission)
                    @include('site._article-card')
                @empty
                    <p class="bg-card p-8 text-muted-foreground md:col-span-2">No published manuscripts match your search.</p>
                @endforelse
            </div>
            {{ $submissions->links() }}
        </div>
    </div>
</x-layouts.site>
