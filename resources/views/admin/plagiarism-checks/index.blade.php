<x-layouts.admin title="Plagiarism Checks">
    <x-admin.heading title="Plagiarism Checks" description="Every plagiarism check performed through the system — manuscript-submission checks and paid standalone checks alike." />

    <x-filter-bar>
        <x-filter.search placeholder="Search ID, title, user or manuscript ID…" />
        <x-filter.select name="check_type" label="Check type" :options="App\Enums\PlagiarismCheckType::options()" />
        <x-filter.select name="check_status" label="Check status" :options="App\Enums\PlagiarismCheckStatus::options()" />
        <x-filter.select name="payment_status" label="Payment" :options="App\Enums\PaymentStatus::options() + ['unpaid' => 'No payment']" />
        <x-filter.date-range />
    </x-filter-bar>

    <x-table :columns="['ID', 'Check Type', 'Manuscript ID', 'User', 'Title / Content', 'Similarity (%)', 'Check Status', 'Payment Status', 'Checked At', 'Actions']" :rows="$checks">
        @foreach ($checks as $check)
            <tr>
                <td class="font-mono text-sm">{{ $check->id }}</td>
                <td><x-badge :tone="$check->check_type->value === 'manuscript' ? 'info' : 'gold'">{{ $check->check_type->label() }}</x-badge></td>
                <td class="whitespace-nowrap font-mono text-sm">{{ $check->submission?->reference() ?? '—' }}</td>
                <td class="whitespace-nowrap">{{ $check->user->name }}</td>
                <td class="max-w-xs text-sm">{{ Str::limit($check->title, 60) }}</td>
                <td><x-similarity :value="$check->similarity_percentage" :threshold="$threshold" /></td>
                <td><x-status-badge :status="$check->check_status" /></td>
                <td>@if ($check->payment)<x-status-badge :status="$check->payment->payment_status" />@else<span class="text-sm text-muted-foreground">—</span>@endif</td>
                <td class="whitespace-nowrap text-sm">{{ format_date($check->checked_at, true) }}</td>
                <td><x-action-link :href="route('admin.plagiarism-checks.show', $check)" icon="eye">View</x-action-link></td>
            </tr>
        @endforeach
    </x-table>
    {{ $checks->links() }}
</x-layouts.admin>
