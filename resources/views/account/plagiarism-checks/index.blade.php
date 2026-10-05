<x-layouts.account title="Plagiarism Checks" intro="Standalone checks you have run from the Plagiarism Checker.">
    <div class="flex justify-end"><x-button variant="primary" icon="scan-search" :href="page_url('plagiarism_checker')">New check</x-button></div>
    <x-table class="mt-6" :columns="['ID', 'Title', 'Similarity', 'Status', 'Payment', 'Created', '']" :rows="$checks" empty="No standalone checks yet.">
        @foreach ($checks as $check)
            <tr>
                <td class="font-mono text-sm">{{ $check->id }}</td>
                <td>{{ Str::limit($check->title, 60) }}</td>
                <td><x-similarity :value="$check->similarity_percentage" /></td>
                <td><x-status-badge :status="$check->check_status" /></td>
                <td>@if ($check->payment)<x-status-badge :status="$check->payment->payment_status" />@else<a href="{{ route('account.checkout.plagiarism', $check) }}" class="text-sm text-primary hover:underline">Pay to run</a>@endif</td>
                <td class="text-sm">{{ format_date($check->created_at) }}</td>
                <td><x-action-link :href="route('account.plagiarism-checks.show', $check)" icon="eye">View</x-action-link></td>
            </tr>
        @endforeach
    </x-table>
    {{ $checks->links() }}
</x-layouts.account>
