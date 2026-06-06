<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('invoice.tax_invoice') }} — {{ $invoice['booking']['invoice_no'] ?? $invoice['booking']['display_id'] }}</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #1e293b;
            background: #f1f5f9;
        }

        .invoice-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem 1.5rem;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
        }

        .invoice-toolbar a {
            color: #475569;
            text-decoration: none;
            font-size: 0.875rem;
        }

        .invoice-toolbar a:hover { color: #2563eb; }

        .invoice-toolbar-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            background: #2563eb;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-print:hover { background: #1d4ed8; }

        .invoice-page {
            max-width: 900px;
            margin: 1.5rem auto;
            padding: 0 1rem 2rem;
        }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .invoice-page { margin: 0; padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="invoice-toolbar no-print">
        <a href="{{ route('bookings.show', $invoice['booking']['id']) }}">&larr; {{ __('invoice.back_to_booking') }}</a>
        <div class="invoice-toolbar-actions">
            <button type="button" class="btn-print" onclick="window.print()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                {{ __('invoice.print_invoice') }}
            </button>
        </div>
    </div>

    <div class="invoice-page">
        <x-invoice.template :data="$invoice" />
    </div>
</body>
</html>
