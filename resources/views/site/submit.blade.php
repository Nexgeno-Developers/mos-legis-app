@php
    // All wording comes from Admin → Pages → Submit; an empty field hides its element.
    // {fee}, {threshold} and {currency} are filled in from Settings.
    $threshold = settings('manuscript.plagiarism_max_similarity_percent');
    $fill = fn (?string $text) => strtr((string) $text, [
        '{fee}' => money($prescreeningFee),
        '{threshold}' => rtrim(rtrim(number_format((float) $threshold, 2), '0'), '.'),
        '{currency}' => settings('payment.currency'),
    ]);
    $m = fn (string $key) => $page->meta($key);
    $text = fn (string $key) => $fill($m($key));
    $rows = fn (string $key) => collect($m($key) ?: [])->filter(fn ($row) => implode('', (array) $row) !== '')->values();

    $facts = $rows('facts');
    $steps = $rows('steps');
    $checklist = $rows('checklist')->pluck('text')->filter();
    $guidelines = filled($m('guidelines_slug')) ? App\Support\PublicPages::bySlug($m('guidelines_slug')) : null;
    $factIcons = ['badge-indian-rupee', 'file-text', 'shield-check'];

    // Quick links to the guide sections that have a heading.
    $guide = array_filter([
        'process' => $steps->isNotEmpty() ? ($m('steps_label') ?: $m('steps_heading')) : null,
        'categories' => $m('categories_label') ?: $m('categories_heading'),
        'preparation' => $m('preparation_label') ?: $m('preparation_heading'),
        'fees' => $m('fees_label') ?: $m('fees_heading'),
        'more' => $page->content ? ($m('more_heading') ?: $m('more_label')) : null,
    ]);
@endphp
<x-layouts.site :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: $page->excerpt">
    <x-page-header :title="$page->title" :intro="$page->excerpt" />

    <div class="mx-auto max-w-[1200px] px-4 py-8 sm:px-6 md:py-10">
        {{-- 1. The form comes first --}}
        <section id="submission-form" class="scroll-mt-28">
            @if ($facts->isNotEmpty())
                <ul @class(['mb-6 grid gap-px border border-border bg-border text-sm', 'sm:grid-cols-2' => $facts->count() === 2, 'sm:grid-cols-3' => $facts->count() >= 3])>
                    @foreach ($facts as $fact)
                        <li class="flex items-start gap-3 bg-card px-4 py-3">
                            <x-icon :name="$factIcons[$loop->index % 3]" class="mt-0.5 h-5 w-5 shrink-0 text-primary" />
                            <span>
                                @if (filled($fact['title'] ?? null))<strong>{{ $fill($fact['title']) }}</strong>@endif
                                @if (filled($fact['text'] ?? null))<span class="block text-muted-foreground">{{ $fill($fact['text']) }}</span>@endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif

            @if ($isAuthor)
                @if ($m('form_label') || $m('form_heading'))
                    <div class="mb-6"><x-section-heading :eyebrow="$text('form_label')" :title="$text('form_heading')" /></div>
                @endif
                @include('submissions._step-form', ['authorCategories' => $authorCategories->pluck('name', 'id')])
            @elseif (auth()->check())
                <p class="border-l-2 border-gold/60 bg-card px-4 py-3">Manuscripts are submitted from an author account. Staff accounts cannot submit.</p>
            @else
                <div class="flex flex-wrap items-center justify-between gap-6 border border-border bg-card p-6 md:p-8">
                    <div class="max-w-xl">
                        @if ($m('form_heading'))<p class="label-caps text-xs text-primary">{{ $text('form_heading') }}</p>@endif
                        @if ($m('guest_heading'))<p class="mt-1 font-display text-2xl">{{ $text('guest_heading') }}</p>@endif
                        @if ($m('guest_text'))<p class="mt-1 text-muted-foreground">{{ $text('guest_text') }}</p>@endif
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <x-button variant="primary" icon="log-in" :href="route('login')">Sign in</x-button>
                        <x-button icon="user-plus" :href="route('register')">Create account</x-button>
                    </div>
                </div>
            @endif
        </section>

        {{-- 2. Submission guide --}}
        <div class="mt-16 border-t border-border pt-10">
            @if ($m('guide_label'))<p class="label-caps text-xs text-primary">{{ $text('guide_label') }}</p>@endif
            @if ($m('guide_heading'))<h2 class="mt-1 font-display text-3xl">{{ $text('guide_heading') }}</h2>@endif
            @if ($guide)
                <nav aria-label="Submission guide" class="mt-5 flex flex-wrap gap-2">
                    @foreach ($guide as $anchor => $label)
                        <a href="#{{ $anchor }}" class="border border-border bg-card px-3 py-1.5 text-sm hover:border-gold hover:text-primary">{{ $fill($label) }}</a>
                    @endforeach
                </nav>
            @endif
        </div>

        <div class="mt-10 space-y-16">
            @if ($steps->isNotEmpty())
                <section id="process" class="scroll-mt-28">
                    <x-section-heading :eyebrow="$text('steps_label')" :title="$text('steps_heading')" />
                    <ol @class(['mt-8 grid gap-px border border-border bg-border sm:grid-cols-2', 'lg:grid-cols-3' => $steps->count() === 3, 'lg:grid-cols-4' => $steps->count() === 4, 'lg:grid-cols-5' => $steps->count() >= 5])>
                        @foreach ($steps as $step)
                            <li class="bg-card p-5">
                                <span class="grid h-8 w-8 place-items-center rounded-full bg-primary font-mono text-xs text-primary-foreground">{{ $loop->iteration }}</span>
                                @if (filled($step['title'] ?? null))<p class="mt-3 font-display text-lg">{{ $fill($step['title']) }}</p>@endif
                                @if (filled($step['text'] ?? null))<p class="mt-1 text-sm text-muted-foreground">{{ $fill($step['text']) }}</p>@endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            <section id="categories" class="scroll-mt-28">
                <x-section-heading :eyebrow="$text('categories_label')" :title="$text('categories_heading')" />
                <div class="mt-8 grid gap-4 md:grid-cols-2">
                    @foreach ($contentCategories as $category)
                        <div class="flex flex-col border border-border bg-card p-6">
                            <div class="flex items-baseline justify-between gap-4">
                                <p class="font-display text-xl">{{ $category->name }}</p>
                                <span class="shrink-0 border border-border px-2 py-0.5 font-mono text-xs text-muted-foreground">{{ $category->wordLimitLabel() }}</span>
                            </div>
                            @if ($category->guideline)<p class="mt-2 text-sm text-muted-foreground">{{ $category->guideline }}</p>@endif
                            @if ($category->currentTheme)
                                <p class="mt-4 border-l-2 border-gold/60 pl-3 text-sm"><span class="label-caps block text-xs text-primary">This month's theme</span>{{ $category->currentTheme->fullLabel() }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section id="preparation" class="scroll-mt-28">
                <x-section-heading :eyebrow="$text('preparation_label')" :title="$text('preparation_heading')" />
                <div @class(['mt-8 grid gap-8', 'lg:grid-cols-[minmax(0,1fr)_22rem]' => $checklist->isNotEmpty()])>
                    <div class="prose-legis">
                        @if ($guidelines)
                            {!! Str::limit(strip_tags($guidelines->content, '<p><ul><li><h2><strong><em>'), 1200) !!}
                            <p><a href="{{ $guidelines->url() }}">Read the full {{ $guidelines->title }}</a></p>
                        @elseif ($m('preparation_text'))
                            <p class="whitespace-pre-line">{{ $text('preparation_text') }}</p>
                        @endif
                    </div>
                    @if ($checklist->isNotEmpty())
                        <div class="h-fit border border-border bg-card p-6">
                            @if ($m('checklist_heading'))<h3 class="font-display text-xl">{{ $text('checklist_heading') }}</h3>@endif
                            <ul class="mt-4 space-y-2.5 text-sm">
                                @foreach ($checklist as $item)
                                    <li class="flex gap-2"><x-icon name="check" class="mt-0.5 shrink-0 text-success" /> {{ $fill($item) }}</li>
                                @endforeach
                            </ul>
                            <a href="#submission-form" class="label-caps mt-5 inline-flex items-center gap-1 text-xs text-primary hover:underline">Go to the form <x-icon name="arrow-right" class="h-3.5 w-3.5" /></a>
                        </div>
                    @endif
                </div>
            </section>

            <section id="fees" class="scroll-mt-28">
                <x-section-heading :eyebrow="$text('fees_label')" :title="$text('fees_heading')" />
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="border border-border bg-card p-5">
                        <p class="label-caps text-xs text-muted-foreground">On submission</p>
                        <p class="mt-1 font-display text-2xl">{{ money($prescreeningFee) }}</p>
                        @if ($m('fees_submission'))<p class="text-sm text-muted-foreground">{{ $text('fees_submission') }}</p>@endif
                    </div>
                    <div class="border border-border bg-card p-5">
                        <p class="label-caps text-xs text-muted-foreground">After acceptance</p>
                        <p class="mt-1 font-display text-2xl">Publication fee</p>
                        @if ($m('fees_publication'))<p class="text-sm text-muted-foreground">{{ $text('fees_publication') }}</p>@endif
                    </div>
                </div>
                @if ($m('fees_note'))<p class="mt-4 text-sm text-muted-foreground">{{ $text('fees_note') }}</p>@endif
                <div class="mt-4 overflow-x-auto border border-border bg-card">
                    <table class="w-full min-w-[640px] text-left text-sm">
                        <thead><tr class="border-b border-border bg-secondary/60"><th class="label-caps px-4 py-3 text-xs text-muted-foreground">Author category</th>
                            @foreach ($contentCategories as $category)<th class="label-caps px-4 py-3 text-xs text-muted-foreground">{{ $category->name }}</th>@endforeach</tr></thead>
                        <tbody>
                            @foreach ($authorCategories as $author)
                                <tr class="border-b border-border last:border-0">
                                    <th class="px-4 py-3 font-medium">{{ $author->name }}</th>
                                    @foreach ($contentCategories as $category)
                                        @php $fee = $fees["{$author->id}-{$category->id}"] ?? null; @endphp
                                        <td class="px-4 py-3">{!! $fee === null ? '<span class="text-muted-foreground">—</span>' : e(money($fee)) !!}</td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            @if ($page->content)
                <section id="more" class="scroll-mt-28">
                    <x-section-heading :eyebrow="$text('more_label')" :title="$text('more_heading')" />
                    <div class="prose-legis measure mt-6">{!! $page->content !!}</div>
                </section>
            @endif

            @if ($m('cta_text') || $m('cta_button'))
                <div class="flex flex-wrap items-center justify-between gap-4 border border-gold/50 bg-card px-6 py-5">
                    @if ($m('cta_text'))<p class="font-display text-xl">{{ $text('cta_text') }}</p>@endif
                    @if ($m('cta_button'))<x-button variant="primary" icon="arrow-right" href="#submission-form">{{ $text('cta_button') }}</x-button>@endif
                </div>
            @endif
        </div>
    </div>
</x-layouts.site>
