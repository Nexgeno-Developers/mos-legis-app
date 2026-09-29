<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description">
    <x-page-header eyebrow="People" :title="$page->title" :intro="$page->excerpt" />
    <div class="mx-auto max-w-[1200px] space-y-16 px-6 py-16">
        @forelse ($groups as $group => $members)
            <section>
                <x-section-heading :eyebrow="$group" :title="match (strtolower($group)) { 'founder', 'founders' => 'Who Started the Review', 'advisory' => 'Counsel to the Board', default => 'Board of Editors' }" />
                <div class="mt-8 grid gap-px bg-border sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($members as $member)
                        <div class="bg-card p-6">
                            <p class="font-display text-xl">{{ $member['name'] }}</p>
                            <p class="label-caps mt-1 text-xs text-primary">{{ $member['designation'] }}</p>
                            <p class="mt-3 text-sm text-muted-foreground">{{ $member['overview'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="text-muted-foreground">The editorial board will be listed here soon.</p>
        @endforelse
        @if ($page->content)<div class="prose-legis measure">{!! $page->content !!}</div>@endif
    </div>
</x-layouts.site>
