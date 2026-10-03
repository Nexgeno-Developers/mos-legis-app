<x-layouts.account title="Payments & Invoices" intro="Every payment you have made, with a downloadable invoice. Publication certificates are on each published manuscript in My Submissions.">
    <x-table :columns="['Invoice', 'For', 'Purpose', 'Total', 'Status', 'Date', '']" :rows="$payments">
        @foreach ($payments as $payment)
            <tr>
                <td class="font-mono text-sm">{{ $payment->invoice_number ?? '—' }}</td>
                <td class="text-sm">{{ $payment->payable instanceof App\Models\ManuscriptSubmission ? $payment->payable->reference() : 'Plagiarism check #'.$payment->payable_id }}</td>
                <td class="text-sm">{{ $payment->payment_purpose->label() }}</td>
                <td>{{ money($payment->total_amount) }}</td>
                <td><x-status-badge :status="$payment->payment_status" /></td>
                <td class="text-sm">{{ format_date($payment->paid_at ?? $payment->created_at) }}</td>
                <td>@if ($payment->isPaid())<a href="{{ route('account.payments.invoice', $payment) }}" class="text-sm text-primary hover:underline">Download</a>@endif</td>
            </tr>
        @endforeach
    </x-table>
    {{ $payments->links() }}
</x-layouts.account>
