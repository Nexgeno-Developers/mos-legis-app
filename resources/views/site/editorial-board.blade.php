{{-- Editorial board (Teams template): fixed sections — Founders, Editorial Board, Advisory Board. --}}
<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description">
    <x-page-header :title="$page->title" :intro="$page->excerpt" />
    <div class="mx-auto max-w-[1200px] space-y-12 px-4 py-8 sm:px-6 md:py-10">
        @forelse ($groups as $section)
            <section>
                @if ($section['label'] || $section['heading'])
                    <x-section-heading :eyebrow="$section['label']" :title="$section['heading']" class="mb-8" />
                @endif
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($section['members'] as $member)
                        <div class="border border-border bg-card p-6">
                            <p class="font-display text-xl">{{ $member['name'] }}</p>
                            @if (! empty($member['designation']))<p class="label-caps mt-1 text-xs text-primary">{{ $member['designation'] }}</p>@endif
                            @if (! empty($member['overview']))<p class="mt-3 text-sm text-muted-foreground">{{ $member['overview'] }}</p>@endif
                        </div>
                    @endforeach
                </div>
            </section>
        @empty
            <p class="text-muted-foreground">The editorial board will be listed here soon.</p>
        @endforelse
        @if ($page->content)<div class="prose-legis">{!! $page->content !!}</div>@endif
    </div>
</x-layouts.site>