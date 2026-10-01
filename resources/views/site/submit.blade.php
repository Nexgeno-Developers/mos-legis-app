<x-layouts.site :title="$page?->seo_title ?: 'Submit a Manuscript'" :description="$page?->seo_description">
    <x-page-header eyebrow="Submission Portal" :title="$page?->title && $page->title !== 'Submit' ? $page->title : 'Submit a Manuscript'"
        :intro="$page?->excerpt ?: 'Submissions are open year-round across all content categories. Every manuscript is pre-screened for similarity and then sent for double-blind peer review.'" />

    <div class="mx-auto max-w-[1200px] space-y-12 px-4 py-8 sm:px-6 md:py-10">
        @if ($page?->content)<div class="prose-legis measure">{!! $page->content !!}</div>@endif

        <section>
            <x-section-heading eyebrow="Step 1 — Choose a category" title="Categories & Word Limits" />
            <div class="mt-8 grid gap-px bg-border md:grid-cols-2">
                @foreach ($contentCategories as $category)
                    <div class="bg-card p-6">
                        <div class="flex items-baseline justify-between gap-4">
                            <p class="font-display text-xl">{{ $category->name }}</p>
                            <span class="font-mono text-xs whitespace-nowrap text-muted-foreground">{{ $category->wordLimitLabel() }}</span>
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">{{ $category->guideline }}</p>
                        @if ($category->currentTheme)<p class="mt-3 text-sm"><span class="label-caps text-xs text-primary">This month's theme</span><br>{{ $category->currentTheme->fullLabel() }}</p>@endif
                    </div>
                @endforeach
            </div>
        </section>

        <section>
            <x-section-heading eyebrow="Step 2 — Prepare" title="Formatting & Citation Rules" />
            <div class="mt-6 grid gap-8 md:grid-cols-2">
                <div class="prose-legis">
                    @if ($guidelines)
                        {!! Str::limit(strip_tags($guidelines->content, '<p><ul><li><h2><strong><em>'), 1200) !!}
                        <p><a href="{{ route('pages.show', $guidelines->slug) }}">Read the full Author Guidelines</a></p>
                    @endif
                </div>
                <div>
                    <p class="label-caps text-sm text-primary">Before you upload</p>
                    <h3 class="mt-2 font-display text-2xl">Submission Checklist</h3>
                    <ul class="mt-4 space-y-2">
                        @foreach (['Manuscript in .docx format within the category word limit', 'Abstract of not more than 250 words', 'Three to six keywords', 'Co-authors listed and consenting', 'Originality, plagiarism and AI-use declarations ready', 'Billing address for the pre-screening invoice'] as $item)
                            <li class="flex gap-2"><x-icon name="check" class="mt-1.5 text-success" /> {{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </section>

        <section>
            <x-section-heading eyebrow="Fees" title="Fee Structure" />
            <p class="measure mt-4 text-muted-foreground">Plagiarism pre-screening: <strong>{{ money($prescreeningFee) }}</strong>, payable on submission. The publication fee below is payable only after acceptance. Tax at {{ rtrim(rtrim(number_format($taxRate, 2), '0'), '.') }}% is added for Indian billing addresses; international payers are zero-rated. All fees in {{ settings('payment.currency') }}.</p>
            <div class="mt-6 overflow-x-auto border border-border bg-card">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead><tr class="border-b border-border"><th class="label-caps px-4 py-3 text-xs text-muted-foreground">Author category</th>
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

        <section id="submission-form">
            <x-section-heading eyebrow="Step 3 — Submit" title="Manuscript Submission Form" />
            <div class="mt-8">
                @if ($isAuthor)
                    @include('submissions._step-form', ['authorCategories' => $authorCategories->pluck('name', 'id')])
                @elseif (auth()->check())
                    <p class="border-l-2 border-gold/60 bg-card px-4 py-3">Manuscripts are submitted from an author account. Staff accounts cannot submit.</p>
                @else
                    <div class="flex flex-wrap items-center justify-between gap-4 border border-border bg-card p-8">
                        <div>
                            <p class="font-display text-2xl">Sign in to submit</p>
                            <p class="text-muted-foreground">Create a free author account to submit and track your manuscript.</p>
                        </div>
                        <div class="flex gap-3">
                            <x-button variant="primary" icon="log-in" :href="route('login')">Sign in</x-button>
                            <x-button icon="user-plus" :href="route('register')">Create account</x-button>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <section>
            <x-section-heading eyebrow="What happens next" title="From Submission to Publication" />
            <ol class="mt-8 grid gap-px bg-border md:grid-cols-5">
                @foreach (['Pay the pre-screening fee' => 'Your manuscript is checked for similarity.', 'Plagiarism screening' => 'Above '.settings('manuscript.plagiarism_max_similarity_percent').'% similarity is declined.', 'Peer review' => 'A subject reviewer is assigned automatically.', 'Revision or approval' => 'Revise and resubmit if requested.', 'Publication' => 'Pay the publication fee; receive your certificate.'] as $title => $text)
                    <li class="bg-card p-5"><span class="font-mono text-xs text-primary">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><p class="mt-2 font-display text-lg">{{ $title }}</p><p class="mt-1 text-sm text-muted-foreground">{{ $text }}</p></li>
                @endforeach
            </ol>
        </section>
    </div>
</x-layouts.site>
