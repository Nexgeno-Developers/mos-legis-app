@php $b = $payment->billing_details ?? []; @endphp
<x-layouts.admin :title="'Payment #'.$payment->id">
    <x-admin.heading :title="'Payment #'.$payment->id" :description="$payment->payment_purpose->label().' fee · '.$payment->user->name">
        <x-slot:actions>
            <x-button :href="route('admin.payments.invoice', $payment)" target="_blank" icon="file-text">Invoice PDF</x-button>
            <x-button :href="route('admin.payments.index')" icon="arrow-left">Back</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <div class="mt-8 grid gap-8 lg:grid-cols-2">
        <x-admin.panel title="Payment">
            <x-dl :items="[
                'Status' => e($payment->payment_status->label()),
                'Invoice number' => e($payment->invoice_number),
                'Amount' => money($payment->amount),
                'Tax' => $payment->hasTax() ? money($payment->tax_amount).' ('.(float) $payment->tax_rate.'%)' : 'Zero-rated',
                'Total' => '<strong>'.money($payment->total_amount).'</strong>',
                'Currency' => e($payment->currency),
                'Method' => e($payment->payment_method ? ucfirst($payment->payment_method) : null),
                'Details' => e($payment->payment_details),
                'Gateway order' => e($payment->gateway_order_id),
                'Gateway payment ID' => e($payment->payment_id),
                'Paid at' => format_date($payment->paid_at, true),
                'Remarks' => e($payment->remarks),
            ]" />
        </x-admin.panel>
        <x-admin.panel title="Billing snapshot" description="Frozen at the time of payment; later address edits do not change it.">
            <x-dl :items="[
                'Name' => e($b['name'] ?? null),
                'Organisation' => e($b['organization_name'] ?? null),
                'Address' => e(collect([$b['address_line1'] ?? null, $b['address_line2'] ?? null, $b['city'] ?? null, $b['state'] ?? null, $b['postal_code'] ?? null])->filter()->implode(', ')),
                'Country' => e($payment->billing_country_code),
                ($payment->billing_country_code === 'IN' ? 'GSTIN' : 'Tax ID') => e($b['tax_id_number'] ?? null),
                'Payer email' => e($payment->user->email),
            ]" />
            @if ($payment->payable instanceof App\Models\ManuscriptSubmission)
                <p class="mt-6"><x-button size="sm" :href="route('admin.submissions.show', $payment->payable)" icon="inbox">Manuscript {{ $payment->payable->reference() }}</x-button></p>
            @elseif ($payment->payable)
                <p class="mt-6"><x-button size="sm" :href="route('admin.plagiarism-checks.show', $payment->payable)" icon="scan-search">Plagiarism check #{{ $payment->payable_id }}</x-button></p>
            @endif
        </x-admin.panel>
    </div>
</x-layouts.admin>
