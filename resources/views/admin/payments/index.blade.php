<x-layouts.admin title="Payments">
    <x-admin.heading title="Manuscript Payments" description="Every pre-screening fee, publication fee and standalone plagiarism-check payment across the platform.">
        <x-slot:actions>
            <div class="border border-border bg-card px-4 py-2 text-right">
                <p class="label-caps text-[0.65rem] text-muted-foreground">Total received</p>
                <p class="font-display text-xl">{{ money($totalPaid) }}</p>
            </div>
        </x-slot:actions>
    </x-admin.heading>

    <x-filter-bar>
        <x-filter.search placeholder="Search ID, payment ID or invoice…" />
        <x-filter.text name="submission" label="Manuscript ID" placeholder="MOS-00042" />
        <x-filter.text name="user" label="User" placeholder="Name or email" />
        <x-filter.select name="purpose" label="Type" :options="App\Enums\PaymentPurpose::options()" />
        <x-filter.select name="status" label="Status" :options="App\Enums\PaymentStatus::options()" />
        <x-filter.select name="method" label="Method" :options="$methods" />
        <x-filter.date-range />
    </x-filter-bar>

    <x-table :columns="['ID', 'Manuscript (Submission) ID', 'User', 'Type', 'Amount', 'Payment Method', 'Payment Status', 'Payment ID', 'Paid At', 'Remarks', 'Actions']" :rows="$payments">
        @foreach ($payments as $payment)
            <tr>
                <td class="font-mono text-sm">{{ $payment->id }}</td>
                <td class="whitespace-nowrap font-mono text-sm">
                    @if ($payment->payable instanceof App\Models\ManuscriptSubmission)
                        <a class="hover:text-primary" href="{{ route('admin.submissions.show', $payment->payable) }}">{{ $payment->payable->reference() }}</a>
                    @else
                        <span class="text-muted-foreground">Standalone check #{{ $payment->payable_id }}</span>
                    @endif
                </td>
                <td class="whitespace-nowrap">{{ $payment->user->name }}</td>
                <td class="whitespace-nowrap text-sm">{{ $payment->payment_purpose->label() }}</td>
                <td class="whitespace-nowrap">{{ money($payment->total_amount) }}@if ($payment->hasTax())<span class="block text-xs text-muted-foreground">incl. {{ money($payment->tax_amount) }} tax</span>@endif</td>
                <td class="text-sm">{{ $payment->payment_method ? ucfirst($payment->payment_method) : '—' }}</td>
                <td><x-status-badge :status="$payment->payment_status" /></td>
                <td class="font-mono text-xs">{{ $payment->payment_id ?? '—' }}</td>
                <td class="whitespace-nowrap text-sm">{{ format_date($payment->paid_at, true) }}</td>
                <td class="max-w-[12rem] text-sm text-muted-foreground">{{ $payment->remarks ?? '—' }}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <x-action-link :href="route('admin.payments.show', $payment)" icon="eye">View</x-action-link>
                        <x-action-link :href="route('admin.payments.invoice', $payment)" icon="file-text" target="_blank">Invoice</x-action-link>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-table>
    {{ $payments->links() }}
</x-layouts.admin>
