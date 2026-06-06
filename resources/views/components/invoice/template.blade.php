@props(['data'])

@php
    $isPaid = ($data['booking']['status'] ?? '') === 'paid';
    $paymentStatusLabel = match ($data['booking']['status'] ?? '') {
        'paid' => __('invoice.paid'),
        'partially_paid' => __('invoice.partially_paid'),
        default => __('invoice.unpaid'),
    };
    $paymentStatusClass = match ($data['booking']['status'] ?? '') {
        'paid' => 'invoice-badge--paid',
        'partially_paid' => 'invoice-badge--partial',
        default => 'invoice-badge--unpaid',
    };
    $durationLabel = ($data['booking']['duration'][0] ?? null) && ($data['booking']['duration'][1] ?? null)
        ? $data['booking']['duration'][0].' – '.$data['booking']['duration'][1]
        : __('invoice.not_available');
    $cabinsAndSeats = ! empty($data['booking']['cabins_and_seats'])
        ? implode(', ', $data['booking']['cabins_and_seats'])
        : __('invoice.not_available');
@endphp

<style>
    .invoice {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .invoice-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 2rem;
        padding: 2rem 2.5rem;
        background: #2563eb;
        color: #fff;
    }

    .invoice-company {
        flex: 1;
        min-width: 0;
    }

    .invoice-company-brand {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .invoice-company-logo {
        width: 40px;
        height: 40px;
        object-fit: contain;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 6px;
        padding: 4px;
    }

    .invoice-company-name {
        font-size: 1.375rem;
        font-weight: 700;
        line-height: 1.2;
    }

    .invoice-company-meta {
        font-size: 0.8125rem;
        line-height: 1.6;
        opacity: 0.92;
    }

    .invoice-title-block {
        text-align: right;
        flex-shrink: 0;
    }

    .invoice-title-label {
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        opacity: 0.85;
        margin-bottom: 0.25rem;
    }

    .invoice-number {
        font-size: 1.375rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .invoice-date {
        font-size: 0.8125rem;
        opacity: 0.9;
        margin-bottom: 0.75rem;
    }

    .invoice-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .invoice-badge--paid {
        background: rgba(34, 197, 94, 0.2);
        color: #bbf7d0;
    }

    .invoice-badge--partial {
        background: rgba(234, 179, 8, 0.2);
        color: #fef08a;
    }

    .invoice-badge--unpaid {
        background: rgba(239, 68, 68, 0.2);
        color: #fecaca;
    }

    .invoice-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .invoice-info-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        padding: 1.75rem 2.5rem;
        border-bottom: 1px solid #e2e8f0;
    }

    .invoice-info-block h3 {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.75rem;
    }

    .invoice-info-block p {
        font-size: 0.8125rem;
        color: #334155;
        margin-bottom: 0.25rem;
    }

    .invoice-info-block p strong {
        font-weight: 600;
        color: #0f172a;
    }

    .invoice-table-wrap {
        padding: 0 2.5rem;
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1.5rem;
    }

    .invoice-table thead th {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #64748b;
        text-align: left;
        padding: 0.75rem 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .invoice-table thead th:not(:first-child) {
        text-align: right;
    }

    .invoice-table tbody td {
        padding: 1rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.8125rem;
        vertical-align: top;
    }

    .invoice-table tbody td:not(:first-child) {
        text-align: right;
        white-space: nowrap;
    }

    .invoice-item-title {
        font-weight: 600;
        color: #0f172a;
        margin-bottom: 0.125rem;
    }

    .invoice-item-subtitle {
        font-size: 0.75rem;
        color: #64748b;
    }

    .invoice-totals-wrap {
        display: flex;
        justify-content: flex-end;
        padding: 1.5rem 2.5rem 2rem;
        position: relative;
    }

    .invoice-totals {
        width: 280px;
    }

    .invoice-totals-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.375rem 0;
        font-size: 0.8125rem;
        color: #475569;
    }

    .invoice-totals-row--total {
        margin-top: 0.5rem;
        padding-top: 0.75rem;
        border-top: 2px solid #e2e8f0;
        font-size: 1rem;
        font-weight: 700;
        color: #2563eb;
    }

    .invoice-totals-row--balance {
        font-weight: 600;
        color: #16a34a;
    }

    .invoice-paid-stamp {
        position: absolute;
        right: 320px;
        bottom: 1rem;
        transform: rotate(-12deg);
        border: 3px solid #22c55e;
        color: #22c55e;
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: 0.15em;
        padding: 0.25rem 1.5rem;
        border-radius: 4px;
        opacity: 0.35;
        pointer-events: none;
        user-select: none;
    }

    .invoice-footer {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        padding: 1.75rem 2.5rem;
        border-top: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .invoice-footer h3 {
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.75rem;
    }

    .invoice-footer p {
        font-size: 0.8125rem;
        color: #475569;
        line-height: 1.6;
    }

    .invoice-bottom-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 2.5rem;
        background: #f1f5f9;
        font-size: 0.75rem;
        color: #64748b;
        border-top: 1px solid #e2e8f0;
    }

    @media (max-width: 768px) {
        .invoice-header {
            flex-direction: column;
            padding: 1.5rem;
        }

        .invoice-title-block {
            text-align: left;
        }

        .invoice-info-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
            padding: 1.5rem;
        }

        .invoice-table-wrap,
        .invoice-totals-wrap,
        .invoice-footer,
        .invoice-bottom-bar {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .invoice-footer {
            grid-template-columns: 1fr;
        }

        .invoice-paid-stamp {
            display: none;
        }

        .invoice-table {
            font-size: 0.75rem;
        }

        .invoice-table thead {
            display: none;
        }

        .invoice-table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 0.75rem;
        }

        .invoice-table tbody td {
            display: flex;
            justify-content: space-between;
            border: none;
            padding: 0.375rem 0;
        }

        .invoice-table tbody td::before {
            content: attr(data-label);
            font-weight: 600;
            color: #64748b;
            margin-right: 1rem;
        }

        .invoice-table tbody td:first-child {
            display: block;
            margin-bottom: 0.5rem;
        }

        .invoice-table tbody td:first-child::before {
            display: none;
        }
    }

    @media print {
        .invoice {
            box-shadow: none;
            border-radius: 0;
        }
    }
</style>

<div class="invoice">
    {{-- Header --}}
    <div class="invoice-header">
        <div class="invoice-company">
            <div class="invoice-company-brand">
                @if (! empty($data['company']['logo']))
                    <img src="{{ $data['company']['logo'] }}" alt="" class="invoice-company-logo">
                @endif
                <div class="invoice-company-name">{{ $data['company']['name'] ?? __('invoice.not_available') }}</div>
            </div>
            <div class="invoice-company-meta">
                @if (! empty($data['company']['address']))
                    <div>{{ $data['company']['address'] }}</div>
                @endif
                @if (! empty($data['company']['email']))
                    <div>{{ $data['company']['email'] }}</div>
                @endif
                @if (! empty($data['company']['phone']))
                    <div>{{ $data['company']['phone'] }}</div>
                @endif
                @if (! empty($data['company']['gstin']))
                    <div>{{ __('invoice.gstin') }}: {{ $data['company']['gstin'] }}</div>
                @endif
            </div>
        </div>

        <div class="invoice-title-block">
            <div class="invoice-title-label">{{ __('invoice.tax_invoice') }}</div>
            <div class="invoice-number">{{ $data['booking']['invoice_no'] ?? __('invoice.not_available') }}</div>
            <div class="invoice-date">{{ __('invoice.invoice_date') }}: {{ formatDate($data['booking']['date'] ?? null) ?? __('invoice.not_available') }}</div>
            <span class="invoice-badge {{ $paymentStatusClass }}">
                <span class="invoice-badge-dot"></span>
                {{ $paymentStatusLabel }}
            </span>
        </div>
    </div>

    {{-- Info grid --}}
    <div class="invoice-info-grid">
        <div class="invoice-info-block">
            <h3>{{ __('invoice.bill_to') }}</h3>
            @if (! empty($data['customer']['company_name']))
                <p><strong>{{ $data['customer']['company_name'] }}</strong></p>
            @endif
            <p><strong>{{ $data['customer']['name'] ?? __('invoice.not_available') }}</strong></p>
            @if (! empty($data['customer']['address']))
                <p>{{ $data['customer']['address'] }}</p>
            @endif
            @if (! empty($data['customer']['phone']))
                <p>{{ $data['customer']['phone'] }}</p>
            @endif
            @if (! empty($data['customer']['gstin']))
                <p>{{ __('invoice.gstin') }}: {{ $data['customer']['gstin'] }}</p>
            @endif
        </div>

        <div class="invoice-info-block">
            <h3>{{ __('invoice.booking') }}</h3>
            <p><strong>{{ __('invoice.booking_number') }}:</strong> {{ $data['booking']['display_id'] ?? __('invoice.not_available') }}</p>
            <p><strong>{{ __('invoice.cabins_and_seats') }}:</strong> {{ $cabinsAndSeats }}</p>
            <p><strong>{{ __('invoice.duration') }}:</strong> {{ $durationLabel }}</p>
        </div>

        <div class="invoice-info-block">
            <h3>{{ __('invoice.payment') }}</h3>
            <p><strong>{{ __('invoice.payment_method') }}:</strong> {{ humanize($data['payment']['method'] ?? null) ?? __('invoice.not_available') }}</p>
            <p><strong>{{ __('invoice.payment_date') }}:</strong> {{ formatDate($data['payment']['paid_at'] ?? null) ?? __('invoice.not_available') }}</p>
        </div>
    </div>

    {{-- Items table --}}
    <div class="invoice-table-wrap">
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>{{ __('invoice.description') }}</th>
                    <th>{{ __('invoice.hsn') }}</th>
                    <th>{{ __('invoice.qty') }}</th>
                    <th>{{ __('invoice.rate') }}</th>
                    <th>{{ __('invoice.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data['items'] as $item)
                    <tr>
                        <td data-label="{{ __('invoice.description') }}">
                            <div class="invoice-item-title">{{ $item['description'] }}</div>
                            @if (! empty($item['subtitle']))
                                <div class="invoice-item-subtitle">{{ $item['subtitle'] }}</div>
                            @endif
                        </td>
                        <td data-label="{{ __('invoice.hsn') }}">{{ $item['hsn'] ?? __('invoice.not_available') }}</td>
                        <td data-label="{{ __('invoice.qty') }}">{{ $item['qty'] }}</td>
                        <td data-label="{{ __('invoice.rate') }}">{{ formatCurrency($item['rate']) }}</td>
                        <td data-label="{{ __('invoice.amount') }}">{{ formatCurrency($item['amount']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b; padding: 2rem;">{{ __('invoice.not_available') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="invoice-totals-wrap">
        @if ($isPaid)
            <div class="invoice-paid-stamp">{{ __('invoice.paid') }}</div>
        @endif

        <div class="invoice-totals">
            <div class="invoice-totals-row">
                <span>{{ __('invoice.subtotal') }}</span>
                <span>{{ formatCurrency($data['payment']['subtotal']) }}</span>
            </div>

            @if (($data['payment']['tax_type'] ?? '') === 'cgst_sgst' && ($data['payment']['tax_amount'] ?? 0) > 0)
                <div class="invoice-totals-row">
                    <span>{{ __('invoice.cgst') }} ({{ number_format($data['payment']['cgst_rate'], fmod($data['payment']['cgst_rate'], 1) ? 2 : 0) }}%)</span>
                    <span>{{ formatCurrency($data['payment']['cgst_amount']) }}</span>
                </div>
                <div class="invoice-totals-row">
                    <span>{{ __('invoice.sgst') }} ({{ number_format($data['payment']['sgst_rate'], fmod($data['payment']['sgst_rate'], 1) ? 2 : 0) }}%)</span>
                    <span>{{ formatCurrency($data['payment']['sgst_amount']) }}</span>
                </div>
            @elseif (($data['payment']['tax_type'] ?? '') === 'igst' && ($data['payment']['tax_amount'] ?? 0) > 0)
                <div class="invoice-totals-row">
                    <span>{{ __('invoice.igst') }} ({{ number_format($data['payment']['igst_rate'], fmod($data['payment']['igst_rate'], 1) ? 2 : 0) }}%)</span>
                    <span>{{ formatCurrency($data['payment']['igst_amount']) }}</span>
                </div>
            @endif

            <div class="invoice-totals-row invoice-totals-row--total">
                <span>{{ __('invoice.total_due') }}</span>
                <span>{{ formatCurrency($data['payment']['total']) }}</span>
            </div>

            <div class="invoice-totals-row">
                <span>{{ __('invoice.amount_paid') }}</span>
                <span>-{{ formatCurrency($data['payment']['amount_paid']) }}</span>
            </div>

            <div class="invoice-totals-row invoice-totals-row--balance">
                <span>{{ __('invoice.balance') }}</span>
                <span>{{ formatCurrency($data['payment']['balance']) }}</span>
            </div>
        </div>
    </div>

    {{-- Footer --}}
    <div class="invoice-footer">
        <div>
            <h3>{{ __('invoice.notes') }}</h3>
            <p>{{ $data['notes'] ?: __('invoice.no_notes') }}</p>
        </div>
        <div>
            <h3>{{ __('invoice.bank_details') }}</h3>
            @if (! empty($data['bank']['account_name']))
                <p>{{ $data['bank']['account_name'] }}</p>
            @endif
            @if (! empty($data['bank']['bank_name']) || ! empty($data['bank']['account_number']))
                <p>
                    {{ $data['bank']['bank_name'] ?? '' }}
                    @if (! empty($data['bank']['bank_name']) && ! empty($data['bank']['account_number']))
                        —
                    @endif
                    {{ $data['bank']['account_number'] ?? '' }}
                </p>
            @endif
            @if (! empty($data['bank']['ifsc']) || ! empty($data['bank']['branch']))
                <p>
                    {{ $data['bank']['ifsc'] ?? '' }}
                    @if (! empty($data['bank']['ifsc']) && ! empty($data['bank']['branch']))
                        —
                    @endif
                    {{ $data['bank']['branch'] ?? '' }}
                </p>
            @endif
        </div>
    </div>

    <div class="invoice-bottom-bar">
        <span>
            {{ $data['company']['name'] ?? '' }}
            @if (! empty($data['company']['gstin']))
                · {{ __('invoice.gstin') }}: {{ $data['company']['gstin'] }}
            @endif
        </span>
        <span>{{ __('invoice.page_of', ['current' => 1, 'total' => 1]) }} · {{ parse_url($data['company']['website'] ?? config('app.url'), PHP_URL_HOST) ?? config('app.url') }}</span>
    </div>
</div>
