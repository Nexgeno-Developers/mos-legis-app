<x-layouts.account title="Certificates & Invoices" intro="Your publication certificates and a receipt for every payment.">
    <x-section-heading eyebrow="Certificates" title="Publication certificates" />
    <div class="mt-6 grid gap-4 md:grid-cols-2">
        @forelse ($certificates as $submission)
            <div class="flex items-center justify-between gap-4 border border-gold/50 bg-card p-5">
                <div>
                    <p class="font-mono text-xs text-muted-foreground">{{ $submission->certificate->certificate_number }}</p>
                    <p class="font-display text-lg">{{ Str::limit($submission->title, 70) }}</p>
                    <p class="text-sm text-muted-foreground">Issued {{ format_date($submission->certificate->issued_at) }}</p>
                </div>
                <x-button size="sm" variant="gold" icon="download" :href="route('account.submissions.certificate', $submission)">PDF</x-button>
            </div>
        @empty
            <p class="text-muted-foreground">Certificates appear here once a manuscript is published.</p>
        @endforelse
    </div>

    <x-section-heading class="mt-14" eyebrow="Invoices" title="Payment history" />
    <x-table class="mt-6" :columns="['Invoice', 'For', 'Purpose', 'Total', 'Status', 'Date', '']" :rows="$payments">
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
