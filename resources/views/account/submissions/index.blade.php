<x-layouts.account title="My Submissions" intro="Track every manuscript from screening to publication. Actions appear when something is needed from you.">
    <div class="flex flex-wrap items-end justify-between gap-4">
        <x-filter-bar class="py-0!">
            <x-filter.search placeholder="Search ID or title…" />
            <x-filter.select name="stage" label="Stage" :options="App\Enums\ManuscriptStage::options()" />
            <x-filter.date-range label="Submitted" />
        </x-filter-bar>
        <x-button variant="primary" icon="plus" :href="route('submit').'#submission-form'">New submission</x-button>
    </div>

    <div class="mt-8 space-y-4">
        @forelse ($submissions as $submission)
            <article class="border border-border bg-card p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <p class="font-mono text-xs text-muted-foreground">{{ $submission->reference() }} · {{ $submission->contentCategory->name }} · submitted {{ format_date($submission->created_at) }}</p>
                        <h2 class="mt-1 font-display text-2xl"><a href="{{ route('account.submissions.show', $submission) }}" class="hover:text-primary">{{ $submission->title }}</a></h2>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <x-status-badge :status="$submission->stage" />
                        <span class="text-sm text-muted-foreground">Waiting {{ (int) $submission->waitingSince()->diffInDays() }}d</span>
                        <span class="text-sm">Similarity: <x-similarity :value="$submission->plagiarism_similarity" /></span>
                    </div>
                </div>
                <div class="mt-5"><x-stage-rail :stage="$submission->stage" /></div>
                <div class="mt-5 flex flex-wrap gap-3">
                    <x-button size="sm" icon="eye" :href="route('account.submissions.show', $submission)">View</x-button>
                    @if ($submission->stage->value === 'pending' && ! $submission->prescreeningPayment?->isPaid() && settings()->bool('payment.payment_enabled'))
                        <x-button size="sm" variant="primary" icon="credit-card" :href="route('account.checkout.submission', [$submission, 'prescreening'])">Pay pre-screening fee</x-button>
                    @elseif ($submission->stage->value === 'revision')
                        <x-button size="sm" variant="primary" icon="upload" :href="route('account.submissions.show', $submission).'#revision'">Upload revision</x-button>
                    @elseif ($submission->stage->value === 'approved' && ! $submission->publicationPayment?->isPaid())
                        <x-button size="sm" variant="primary" icon="credit-card" :href="route('account.checkout.submission', [$submission, 'publication'])">Pay publication fee</x-button>
                    @elseif ($submission->stage->value === 'published')
                        <x-button size="sm" icon="award" :href="route('account.submissions.certificate', $submission)">Certificate</x-button>
                    @endif
                </div>
            </article>
        @empty
            <div class="border border-dashed border-border bg-card p-10 text-center">
                <p class="font-display text-2xl">No manuscripts yet</p>
                <p class="mt-2 text-muted-foreground">Start a submission — only the plagiarism pre-screening fee is payable upfront.</p>
                <x-button class="mt-5" variant="primary" icon="plus" :href="route('submit').'#submission-form'">Submit a manuscript</x-button>
            </div>
        @endforelse
    </div>
    {{ $submissions->links() }}
</x-layouts.account>
