@php
    $threshold = settings('manuscript.plagiarism_max_similarity_percent');
    $guide = ['process' => 'How it works', 'categories' => 'Categories & word limits', 'preparation' => 'Preparing your manuscript', 'fees' => 'Fees'];
    if ($page?->content) {
        $guide['more'] = 'More information';
    }
@endphp
<x-layouts.site :title="$page?->seo_title ?: 'Submit a Manuscript'" :description="$page?->seo_description">
    <x-page-header eyebrow="Submission Portal" :title="$page?->title && $page->title !== 'Submit' ? $page->title : 'Submit a Manuscript'"
        :intro="$page?->excerpt ?: 'Submissions are open year-round across all content categories. Every manuscript is pre-screened for similarity and then sent for double-blind peer review.'" />

    <div class="mx-auto max-w-[1200px] px-4 py-8 sm:px-6 md:py-10">
        {{-- 1. The form comes first --}}
        <section id="submission-form" class="scroll-mt-28">
            {{-- Key facts at a glance --}}
            <ul class="mb-6 grid gap-px border border-border bg-border text-sm sm:grid-cols-3">
                <li class="flex items-start gap-3 bg-card px-4 py-3">
                    <x-icon name="badge-indian-rupee" class="mt-0.5 h-5 w-5 text-primary" />
                    <span><strong>{{ money($prescreeningFee) }}</strong> pre-screening fee<br><span class="text-muted-foreground">inclusive of all taxes · <a href="#fees" class="text-primary hover:underline">all fees</a></span></span>
                </li>
                <li class="flex items-start gap-3 bg-card px-4 py-3">
                    <x-icon name="file-text" class="mt-0.5 h-5 w-5 text-primary" />
                    <span><strong>.docx</strong> within the category word limit<br><span class="text-muted-foreground"><a href="#categories" class="text-primary hover:underline">see word limits</a></span></span>
                </li>
                <li class="flex items-start gap-3 bg-card px-4 py-3">
                    <x-icon name="shield-check" class="mt-0.5 h-5 w-5 text-primary" />
                    <span><strong>Double-blind</strong> peer review<br><span class="text-muted-foreground">after similarity screening (max {{ $threshold }}%)</span></span>
                </li>
            </ul>

            @if ($isAuthor)
                <div class="mb-6"><x-section-heading eyebrow="Submit in three steps" title="Manuscript Submission Form" /></div>
                @include('submissions._step-form', ['authorCategories' => $authorCategories->pluck('name', 'id')])
            @elseif (auth()->check())
                <p class="border-l-2 border-gold/60 bg-card px-4 py-3">Manuscripts are submitted from an author account. Staff accounts cannot submit.</p>
            @else
                <div class="flex flex-wrap items-center justify-between gap-6 border border-border bg-card p-6 md:p-8">
                    <div class="max-w-xl">
                        <p class="label-caps text-xs text-primary">Manuscript Submission Form</p>
                        <p class="mt-1 font-display text-2xl">Sign in to submit</p>
                        <p class="mt-1 text-muted-foreground">Create a free author account to submit your manuscript, pay the pre-screening fee and track every stage of review.</p>
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
            <p class="label-caps text-xs text-primary">Submission guide</p>
            <h2 class="mt-1 font-display text-3xl">Everything you need before you submit</h2>
            <nav aria-label="Submission guide" class="mt-5 flex flex-wrap gap-2">
                @foreach ($guide as $anchor => $label)
                    <a href="#{{ $anchor }}" class="border border-border bg-card px-3 py-1.5 text-sm hover:border-gold hover:text-primary">{{ $label }}</a>
                @endforeach
            </nav>
        </div>

        <div class="mt-10 space-y-16">
            <section id="process" class="scroll-mt-28">
                <x-section-heading eyebrow="How it works" title="From Submission to Publication" />
                <ol class="mt-8 grid gap-px border border-border bg-border sm:grid-cols-2 lg:grid-cols-5">
                    @foreach ([
                        'Submit & pay' => 'Fill in the form and pay the pre-screening fee of '.money($prescreeningFee).'.',
                        'Plagiarism screening' => 'Manuscripts above '.$threshold.'% similarity are declined.',
                        'Peer review' => 'A subject reviewer is assigned automatically.',
                        'Revision or approval' => 'Revise and resubmit if the reviewer asks for changes.',
                        'Publication' => 'Pay the publication fee and receive your certificate.',
                    ] as $title => $text)
                        <li class="bg-card p-5">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-primary font-mono text-xs text-primary-foreground">{{ $loop->iteration }}</span>
                            <p class="mt-3 font-display text-lg">{{ $title }}</p>
                            <p class="mt-1 text-sm text-muted-foreground">{{ $text }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section id="categories" class="scroll-mt-28">
                <x-section-heading eyebrow="Choose a category" title="Categories & Word Limits" />
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
                <x-section-heading eyebrow="Prepare" title="Preparing Your Manuscript" />
                <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
                    <div class="prose-legis">
                        @if ($guidelines)
                            {!! Str::limit(strip_tags($guidelines->content, '<p><ul><li><h2><strong><em>'), 1200) !!}
                            <p><a href="{{ $guidelines->url() }}">Read the full Author Guidelines</a></p>
                        @else
                            <p>Follow the formatting and citation rules of your category, and keep the manuscript anonymous for double-blind review.</p>
                        @endif
                    </div>
                    <div class="h-fit border border-border bg-card p-6">
                        <p class="label-caps text-xs text-primary">Before you upload</p>
                        <h3 class="mt-1 font-display text-xl">Submission Checklist</h3>
                        <ul class="mt-4 space-y-2.5 text-sm">
                            @foreach (['Manuscript in .docx format within the category word limit', 'Abstract of not more than 250 words', 'Three to six keywords', 'Co-authors listed and consenting', 'Originality, plagiarism and AI-use declarations ready', 'Billing address for the pre-screening invoice'] as $item)
                                <li class="flex gap-2"><x-icon name="check" class="mt-0.5 shrink-0 text-success" /> {{ $item }}</li>
                            @endforeach
                        </ul>
                        <a href="#submission-form" class="label-caps mt-5 inline-flex items-center gap-1 text-xs text-primary hover:underline">Go to the form <x-icon name="arrow-right" class="h-3.5 w-3.5" /></a>
                    </div>
                </div>
            </section>

            <section id="fees" class="scroll-mt-28">
                <x-section-heading eyebrow="Fees" title="Fee Structure" />
                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="border border-border bg-card p-5">
                        <p class="label-caps text-xs text-muted-foreground">On submission</p>
                        <p class="mt-1 font-display text-2xl">{{ money($prescreeningFee) }}</p>
                        <p class="text-sm text-muted-foreground">Plagiarism pre-screening fee</p>
                    </div>
                    <div class="border border-border bg-card p-5">
                        <p class="label-caps text-xs text-muted-foreground">After acceptance</p>
                        <p class="mt-1 font-display text-2xl">Publication fee</p>
                        <p class="text-sm text-muted-foreground">Depends on your author category and content category (table below)</p>
                    </div>
                </div>
                <p class="mt-4 text-sm text-muted-foreground">All fees are in {{ settings('payment.currency') }} and inclusive of all taxes.</p>
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

            @if ($page?->content)
                <section id="more" class="scroll-mt-28">
                    <x-section-heading eyebrow="Good to know" title="More Information" />
                    <div class="prose-legis measure mt-6">{!! $page->content !!}</div>
                </section>
            @endif

            <div class="flex flex-wrap items-center justify-between gap-4 border border-gold/50 bg-card px-6 py-5">
                <p class="font-display text-xl">Ready to submit your manuscript?</p>
                <x-button variant="primary" icon="arrow-right" href="#submission-form">Back to the form</x-button>
            </div>
        </div>
    </div>
</x-layouts.site>
