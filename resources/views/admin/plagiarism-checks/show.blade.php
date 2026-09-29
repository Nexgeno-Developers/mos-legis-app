@php $matches = $check->api_response['matches'] ?? []; @endphp
<x-layouts.admin :title="'Plagiarism check #'.$check->id">
    <x-admin.heading :title="'Plagiarism check #'.$check->id" :description="$check->title">
        <x-slot:actions>
            @can('plagiarism-checks.recheck')
                <form method="POST" action="{{ route('admin.plagiarism-checks.recheck', $check) }}">@csrf<x-button type="submit" icon="refresh-cw">Re-check</x-button></form>
            @endcan
            <x-button :href="route('admin.plagiarism-checks.index')" icon="arrow-left">Back</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
        <div class="space-y-8">
            <x-admin.panel title="Result">
                <div class="flex flex-wrap items-center gap-6">
                    <div>
                        <p class="label-caps text-xs text-muted-foreground">Similarity</p>
                        <p class="font-display text-5xl"><x-similarity :value="$check->similarity_percentage" :threshold="$threshold" /></p>
                        <p class="text-sm text-muted-foreground">Threshold {{ $threshold }}%</p>
                    </div>
                    <div class="space-y-1">
                        <x-status-badge :status="$check->check_status" />
                        <p class="text-sm text-muted-foreground">Checked {{ format_date($check->checked_at, true) }}</p>
                    </div>
                    <div class="ml-auto flex flex-wrap gap-2">
                        @if ($check->report_file)<x-button size="sm" icon="download" :href="route('admin.plagiarism-checks.report', $check)">Report PDF</x-button>@endif
                        @if ($check->uploaded_file)<x-button size="sm" icon="file" :href="route('admin.plagiarism-checks.file', $check)">Submitted file</x-button>@endif
                    </div>
                </div>
                <h3 class="label-caps mt-8 text-sm text-primary">Matched sources</h3>
                <ul class="mt-2 divide-y divide-border border border-border">
                    @forelse ($matches as $match)
                        <li class="flex justify-between gap-4 px-4 py-2 text-sm"><span>{{ $match['source'] ?? 'Unknown' }}</span><span class="font-mono">{{ $match['similarity'] ?? '—' }}%</span></li>
                    @empty
                        <li class="px-4 py-2 text-sm text-muted-foreground">No matches reported.</li>
                    @endforelse
                </ul>
                @if ($check->content)
                    <h3 class="label-caps mt-8 text-sm text-primary">Submitted content</h3>
                    <p class="mt-2 max-h-72 overflow-y-auto whitespace-pre-line border border-border bg-background p-4 text-sm">{{ $check->content }}</p>
                @endif
                @if ($check->api_response)
                    <details class="mt-6 text-sm">
                        <summary class="cursor-pointer text-muted-foreground">Raw API response</summary>
                        <pre class="mt-2 overflow-x-auto whitespace-pre-wrap font-mono text-xs">{{ json_encode($check->api_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
                    </details>
                @endif
            </x-admin.panel>

            <x-admin.panel title="Check history">
                <x-table :columns="['ID', 'Status', 'Similarity', 'Created', 'Checked At']" :rows="$history" class="min-w-0">
                    @foreach ($history as $item)
                        <tr @class(['bg-secondary/60' => $item->is($check)])>
                            <td class="font-mono text-sm"><a class="hover:text-primary" href="{{ route('admin.plagiarism-checks.show', $item) }}">{{ $item->id }}</a></td>
                            <td><x-status-badge :status="$item->check_status" /></td>
                            <td><x-similarity :value="$item->similarity_percentage" :threshold="$threshold" /></td>
                            <td class="text-sm">{{ format_date($item->created_at, true) }}</td>
                            <td class="text-sm">{{ format_date($item->checked_at, true) }}</td>
                        </tr>
                    @endforeach
                </x-table>
            </x-admin.panel>
        </div>

        <x-admin.panel title="Details">
            <x-dl class="sm:grid-cols-1!" :items="[
                'Check type' => e($check->check_type->label()),
                'User' => e($check->user->name).'<br><span class=\'text-sm text-muted-foreground\'>'.e($check->user->email).'</span>',
                'Manuscript' => $check->submission ? '<a class=\'text-primary hover:underline\' href=\''.route('admin.submissions.show', $check->submission).'\'>'.e($check->submission->reference()).'</a>' : null,
                'Payment' => $check->payment ? e(money($check->payment->total_amount)).' · '.e($check->payment->payment_status->label()) : null,
                'Created' => format_date($check->created_at, true),
            ]" />
            @if ($check->payment && auth()->user()->can('payments.view'))
                <p class="mt-4"><x-button size="sm" icon="file-text" :href="route('admin.payments.invoice', $check->payment)" target="_blank">Invoice</x-button></p>
            @endif
        </x-admin.panel>
    </div>
</x-layouts.admin>
