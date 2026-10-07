@php $matches = $check->api_response['matches'] ?? []; @endphp
<x-layouts.account :title="'Plagiarism check #'.$check->id" :heading="$check->title">
    <div class="grid gap-8 xl:grid-cols-[minmax(0,1fr)_17rem]">
        <x-admin.panel title="Result">
            @if (! $check->payment_id)
                <p class="text-muted-foreground">Pay the checking fee to run this check.</p>
                <x-button class="mt-4" variant="primary" icon="credit-card" :href="route('account.checkout.plagiarism', $check)">Pay now</x-button>
            @elseif (! $check->isCompleted())
                <p class="text-muted-foreground">Your content is being checked — refresh this page in a moment.</p>
                <p class="mt-2"><x-status-badge :status="$check->check_status" /></p>
            @else
                <p class="label-caps text-xs text-muted-foreground">Similarity</p>
                <p class="font-display text-6xl"><x-similarity :value="$check->similarity_percentage" :threshold="$threshold" /></p>
                <p class="mt-2 text-sm text-muted-foreground">Manuscripts above {{ $threshold }}% are not accepted for review.</p>
                <h3 class="label-caps mt-8 text-sm text-primary">Matched sources</h3>
                <ul class="mt-2 divide-y divide-border border border-border">
                    @forelse ($matches as $match)
                        <li class="flex justify-between gap-4 px-4 py-2 text-sm">
                            <span class="min-w-0">{{ $match['source'] ?? 'Unknown' }}@if (! empty($match['url']))<a href="{{ $match['url'] }}" target="_blank" rel="noopener noreferrer nofollow" class="block truncate text-xs text-primary hover:underline">{{ $match['url'] }}</a>@endif</span>
                            <span class="shrink-0 font-mono">{{ $match['similarity'] ?? '—' }}%</span>
                        </li>
                    @empty
                        <li class="px-4 py-2 text-sm text-muted-foreground">No matches reported.</li>
                    @endforelse
                </ul>
                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($check->report_file)<x-button icon="download" :href="route('account.plagiarism-checks.report', $check)">Download report</x-button>@endif
                    <x-button variant="primary" icon="send" :href="page_url('submit').'#submission-form'">Submit as a manuscript</x-button>
                </div>
            @endif
        </x-admin.panel>
        <x-admin.panel title="Details">
            <x-dl class="sm:grid-cols-1!" :items="[
                'Submitted' => format_date($check->created_at, true),
                'Checked' => format_date($check->checked_at, true),
                'Source' => $check->uploaded_file ? 'Uploaded document' : 'Pasted text',
                'Payment' => $check->payment ? e(money($check->payment->total_amount).' · '.$check->payment->payment_status->label()) : null,
            ]" />
            @if ($check->payment?->isPaid())<p class="mt-4"><a class="text-sm text-primary hover:underline" href="{{ route('account.payments.invoice', $check->payment) }}">Download invoice</a></p>@endif
        </x-admin.panel>
    </div>
</x-layouts.account>
