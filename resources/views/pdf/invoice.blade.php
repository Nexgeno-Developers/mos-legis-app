@php
    $b = $payment->billing_details ?? [];
    $isPaid = $payment->isPaid();
@endphp
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>{{ $payment->invoice_number ?? 'Payment '.$payment->id }}</title>@include('pdf._styles')</head>
<body>
<table>
    <tr>
        <td style="width:60%">
            @if ($logo)<img src="{{ $logo }}" style="height:60px">@endif
            <h2 class="crimson">{{ $appName }}</h2>
            <div class="muted">{!! nl2br(e($appAddress)) !!}</div>
            <div class="muted">{{ $appEmail }} @if ($appPhone) · {{ $appPhone }} @endif</div>
        </td>
        <td class="right">
            <h1>{{ $isPaid ? 'Tax Invoice / Receipt' : 'Payment Advice' }}</h1>
            <div class="caps muted">Invoice number</div><div>{{ $payment->invoice_number ?? '—' }}</div>
            <div class="caps muted" style="margin-top:6px">Payment date</div><div>{{ $payment->paid_at?->format('d M Y H:i') ?? '—' }}</div>
            <div class="caps muted" style="margin-top:6px">Status</div><div>{{ $payment->payment_status->label() }}</div>
        </td>
    </tr>
</table>
<div class="rule"></div>
<table>
    <tr>
        <td style="width:50%">
            <div class="caps muted">Billed to</div>
            <strong>{{ $b['name'] ?? $payment->user->name }}</strong><br>
            @if (! empty($b['organization_name'])){{ $b['organization_name'] }}<br>@endif
            {{ $b['address_line1'] ?? '' }} @if (! empty($b['address_line2'])), {{ $b['address_line2'] }}@endif<br>
            {{ collect([$b['city'] ?? null, $b['state'] ?? null, $b['postal_code'] ?? null])->filter()->implode(', ') }}<br>
            {{ $payment->billing_country_code }}<br>
            {{ $payment->user->email }}
            @if (($b['tax_id_type'] ?? 'none') !== 'none' && ! empty($b['tax_id_number']))<br>{{ strtoupper($b['tax_id_type']) }}: {{ $b['tax_id_number'] }}@endif
        </td>
        <td>
            <div class="caps muted">Payment</div>
            Purpose: {{ $payment->payment_purpose->label() }} fee<br>
            Reference: {{ $payment->payable instanceof \App\Models\ManuscriptSubmission ? 'Manuscript '.$payment->payable->reference() : 'Plagiarism check #'.$payment->payable_id }}<br>
            @if ($payment->payable)<span class="muted">{{ \Illuminate\Support\Str::limit($payment->payable->title, 80) }}</span><br>@endif
            Method: {{ $payment->payment_method ? ucfirst($payment->payment_method) : '—' }} @if ($payment->payment_details) ({{ $payment->payment_details }}) @endif<br>
            Gateway transaction: {{ $payment->payment_id ?? '—' }}
        </td>
    </tr>
</table>
<table class="lines" style="margin-top:18px">
    <thead><tr><th>Description</th><th class="right">Amount ({{ $payment->currency }})</th></tr></thead>
    <tbody>
        <tr><td>{{ $payment->payment_purpose->label() }} fee</td><td class="right">{{ number_format((float) $payment->amount, 2) }}</td></tr>
        {{-- Zero-rated (international) payers get no tax line (clarification #5). --}}
        @if ($payment->hasTax())
            <tr><td>Tax @ {{ rtrim(rtrim(number_format((float) $payment->tax_rate, 2), '0'), '.') }}%</td><td class="right">{{ number_format((float) $payment->tax_amount, 2) }}</td></tr>
        @endif
        <tr><td><strong>Total</strong></td><td class="right"><strong>{{ number_format((float) $payment->total_amount, 2) }}</strong></td></tr>
    </tbody>
</table>
<p class="muted" style="margin-top:30px">This is a computer-generated document and does not require a signature.</p>
</body></html>
