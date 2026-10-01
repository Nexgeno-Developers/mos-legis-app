<x-layouts.account title="Dashboard" heading="Welcome, {{ auth()->user()->name }}">
    @if ($profileIncomplete)
        <a href="{{ route('account.profile.edit') }}" class="mb-8 flex items-center gap-2 border-l-2 border-warning bg-card px-4 py-3 hover:bg-secondary">
            <x-icon name="circle-alert" class="text-warning" /> Set your author category in your profile before submitting a manuscript.
        </a>
    @endif

    <div class="grid gap-px border border-border bg-border grid-cols-2 sm:grid-cols-3">
        <x-stat-card label="Total" :value="$stats['total']" :href="route('account.submissions.index')" />
        <x-stat-card label="Pending" :value="$stats['pending']" />
        <x-stat-card label="In review" :value="$stats['in_review']" />
        <x-stat-card label="Approved" :value="$stats['approved']" />
        <x-stat-card label="Published" :value="$stats['published']" />
        <x-stat-card label="Fees paid" :value="money($paid)" :href="route('account.payments.index')" />
    </div>

    <div class="mt-10 grid gap-10 lg:grid-cols-2">
        <section>
            <x-section-heading eyebrow="Action required" title="Needs your attention" />
            <ul class="mt-4 divide-y divide-border border border-border bg-card">
                @forelse ($actionRequired as $submission)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-4">
                        <div>
                            <a href="{{ route('account.submissions.show', $submission) }}" class="font-medium hover:text-primary">{{ $submission->title }}</a>
                            <p class="text-sm text-muted-foreground">{{ $submission->reference() }} · <x-status-badge :status="$submission->stage" /></p>
                        </div>
                        @if ($submission->stage->value === 'pending' && ! $submission->prescreeningPayment?->isPaid() && settings()->bool('payment.payment_enabled'))
                            <x-button size="sm" variant="primary" icon="credit-card" :href="route('account.checkout.submission', [$submission, 'prescreening'])">Pay pre-screening fee</x-button>
                        @elseif ($submission->stage->value === 'revision')
                            <x-button size="sm" variant="primary" icon="upload" :href="route('account.submissions.show', $submission).'#revision'">Upload revision</x-button>
                        @elseif ($submission->stage->value === 'approved')
                            <x-button size="sm" variant="primary" icon="credit-card" :href="route('account.checkout.submission', [$submission, 'publication'])">Pay publication fee</x-button>
                        @else
                            <span class="text-sm text-muted-foreground">Screening in progress</span>
                        @endif
                    </li>
                @empty
                    <li class="px-5 py-6 text-muted-foreground">Nothing needs your attention right now.</li>
                @endforelse
            </ul>
        </section>
        <section>
            <x-section-heading eyebrow="Recent" title="Latest submissions" />
            <ul class="mt-4 divide-y divide-border border border-border bg-card">
                @forelse ($recent as $submission)
                    <li class="px-5 py-4">
                        <a href="{{ route('account.submissions.show', $submission) }}" class="font-medium hover:text-primary">{{ $submission->title }}</a>
                        <p class="text-sm text-muted-foreground">{{ $submission->contentCategory->name }} · {{ format_date($submission->created_at) }}</p>
                    </li>
                @empty
                    <li class="px-5 py-6 text-muted-foreground">No submissions yet.</li>
                @endforelse
            </ul>
            <x-button class="mt-4" variant="primary" icon="plus" :href="route('submit').'#submission-form'">New submission</x-button>
        </section>
    </div>
</x-layouts.account>
