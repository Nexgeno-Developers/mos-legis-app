@php
    $stage = $submission->stage->value;
    $prescreeningPaid = $submission->payments->contains(fn ($p) => $p->payment_purpose->value === 'prescreening' && $p->isPaid());
    $publicationPaid = $submission->payments->contains(fn ($p) => $p->payment_purpose->value === 'publication' && $p->isPaid());
    $latestRevision = $submission->revisions->sortByDesc('round')->first();
@endphp
<x-layouts.account :title="$submission->reference()" :heading="$submission->title">
    <div class="flex flex-wrap items-center gap-3">
        <span class="font-mono text-sm text-muted-foreground">{{ $submission->reference() }}</span>
        <x-status-badge :status="$submission->stage" />
        @foreach ($submission->awards as $award)<x-badge tone="gold">Best Paper · {{ $award->periodLabel() }}</x-badge>@endforeach
        <x-button size="sm" class="ml-auto" icon="arrow-left" :href="route('account.submissions.index')">All submissions</x-button>
    </div>
    <div class="mt-6"><x-stage-rail :stage="$submission->stage" /></div>

    {{-- Actions --}}
    @if ($stage === 'pending' && ! $prescreeningPaid && $paymentsEnabled)
        <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border border-gold/60 bg-card p-6">
            <div>
                <p class="font-display text-xl">Pay the plagiarism pre-screening fee</p>
                <p class="text-muted-foreground">{{ money($prescreeningFee) }} (+ tax for Indian billing addresses). Screening starts as soon as payment is received.</p>
            </div>
            <x-button variant="primary" icon="credit-card" :href="route('account.checkout.submission', [$submission, 'prescreening'])">Pay now</x-button>
        </div>
    @elseif ($stage === 'pending')
        <p class="mt-8 border-l-2 border-gold/60 bg-card px-4 py-3">Plagiarism screening in progress — you will be notified of the result.</p>
    @elseif ($stage === 'approved' && ! $publicationPaid)
        <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border border-success/50 bg-card p-6">
            <div>
                <p class="font-display text-xl text-success">Accepted for publication</p>
                <p class="text-muted-foreground">Publication fee: {{ money($publicationFee) }} (+ tax for Indian billing addresses). Your manuscript is published once payment is received.</p>
            </div>
            <x-button variant="primary" icon="credit-card" :href="route('account.checkout.submission', [$submission, 'publication'])">Pay publication fee</x-button>
        </div>
    @elseif ($stage === 'published' && $submission->certificate)
        <div class="mt-8 flex flex-wrap items-center justify-between gap-4 border border-gold/60 bg-card p-6">
            <div>
                <p class="font-display text-xl">Published {{ format_date($submission->published_at) }}</p>
                <p class="text-muted-foreground">Certificate {{ $submission->certificate->certificate_number }} · <a href="{{ $submission->certificate->verificationUrl() }}" class="text-primary hover:underline" target="_blank">verification link</a></p>
            </div>
            <x-button variant="gold" icon="award" :href="route('account.submissions.certificate', $submission)">Download certificate</x-button>
        </div>
    @endif

    @if ($stage === 'revision')
        <section id="revision" class="mt-8 border border-warning/50 bg-card p-6">
            <h2 class="font-display text-2xl">Revision requested</h2>
            @if ($latestRevision?->reviewer_remarks)
                <blockquote class="mt-3 border-l-2 border-gold pl-4 whitespace-pre-line text-muted-foreground">{{ $latestRevision->reviewer_remarks }}</blockquote>
            @endif
            <form method="POST" action="{{ route('account.submissions.resubmit', $submission) }}" enctype="multipart/form-data" class="mt-5 grid gap-5 md:grid-cols-2" x-data="docxWordCount()">
                @csrf
                <x-form.field label="Revised manuscript (.docx)" name="manuscript" required :hint="'Word limit: '.$submission->contentCategory->wordLimitLabel()">
                    <input type="file" name="manuscript" accept=".docx" class="field-input" required @change="count($event, '#revision_words')">
                    <p class="text-sm text-muted-foreground">Words counted: <input id="revision_words" class="w-24 bg-transparent" readonly></p>
                    <p x-show="error" x-text="error" class="text-sm text-destructive"></p>
                </x-form.field>
                <x-form.textarea name="author_response" label="Response to the reviewer (optional)" rows="3" />
                <div class="md:col-span-2"><x-button type="submit" variant="primary" icon="upload">Submit revision</x-button></div>
            </form>
        </section>
    @endif

    <div class="mt-10 grid gap-8 xl:grid-cols-[minmax(0,1fr)_17rem]">
        <div class="space-y-8">
            <x-admin.panel title="Manuscript information">
                <x-dl :items="[
                    'Content category' => e($submission->contentCategory->name),
                    'Theme' => e($submission->theme?->fullLabel()),
                    'Word count' => number_format($submission->word_count),
                    'Keywords' => e(implode(', ', $submission->keywords ?? [])),
                    'Author category' => e($submission->authorCategory->name),
                    'Institution' => e($submission->institution),
                    'Country' => e($submission->country),
                    'Co-authors' => e(implode(', ', $submission->co_authors ?? [])),
                    'Submitted' => format_date($submission->created_at, true),
                    'Plagiarism similarity' => $submission->plagiarism_similarity !== null ? number_format((float) $submission->plagiarism_similarity, 2).'%' : 'Not checked yet',
                ]" />
                <p class="label-caps mt-6 text-xs text-muted-foreground">Abstract</p>
                <p class="mt-1 whitespace-pre-line">{{ $submission->abstract }}</p>
                <x-button class="mt-6" size="sm" icon="download" :href="route('account.submissions.download', $submission)">Download my manuscript</x-button>
            </x-admin.panel>

            <x-admin.panel title="Reviewer feedback">
                @forelse ($submission->revisions as $revision)
                    <div class="border-l-2 border-gold/60 pl-4 [&:not(:first-child)]:mt-5">
                        <p class="text-sm text-muted-foreground">Round {{ $revision->round }} · {{ format_date($revision->decided_at) }}</p>
                        <p class="mt-1"><x-status-badge :status="$revision->decision" /></p>
                        @if ($revision->reviewer_remarks)<p class="mt-2 whitespace-pre-line">{{ $revision->reviewer_remarks }}</p>@endif
                        @if ($revision->resubmitted_at)<p class="mt-2 text-sm text-muted-foreground">You resubmitted on {{ format_date($revision->resubmitted_at) }}.</p>@endif
                    </div>
                @empty
                    <p class="text-muted-foreground">No reviewer feedback yet.</p>
                @endforelse
            </x-admin.panel>
        </div>
        <div class="grid content-start gap-8 md:grid-cols-2 xl:grid-cols-1">
            <x-admin.panel title="Payments">
                <ul class="space-y-3">
                    @forelse ($submission->payments->sortByDesc('id') as $payment)
                        <li class="flex items-center justify-between gap-2 text-sm">
                            <span>{{ $payment->payment_purpose->label() }}<br><span class="text-muted-foreground">{{ money($payment->total_amount) }} · {{ $payment->payment_status->label() }}</span></span>
                            @if ($payment->isPaid())<a href="{{ route('account.payments.invoice', $payment) }}" class="text-primary hover:underline">Invoice</a>@endif
                        </li>
                    @empty
                        <li class="text-sm text-muted-foreground">No payments yet.</li>
                    @endforelse
                </ul>
            </x-admin.panel>
            <x-admin.panel title="Plagiarism screening">
                @forelse ($submission->plagiarismChecks->sortByDesc('id')->take(1) as $check)
                    <p class="text-sm"><x-status-badge :status="$check->check_status" /></p>
                    @if ($check->isCompleted())<p class="mt-2 font-display text-3xl"><x-similarity :value="$check->similarity_percentage" /></p>@endif
                @empty
                    <p class="text-sm text-muted-foreground">Runs after the pre-screening fee is paid.</p>
                @endforelse
            </x-admin.panel>
        </div>
    </div>
</x-layouts.account>
