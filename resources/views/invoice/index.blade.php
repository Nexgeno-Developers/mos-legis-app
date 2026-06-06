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

        .btn-invoice {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid transparent;
            line-height: 1.25;
        }

        .btn-invoice:disabled {
            opacity: 0.65;
            cursor: not-allowed;
        }

        .btn-invoice--primary {
            background: #2563eb;
            color: #fff;
            border-color: #2563eb;
        }

        .btn-invoice--primary:hover:not(:disabled) { background: #1d4ed8; }

        .btn-invoice--secondary {
            background: #fff;
            color: #334155;
            border-color: #cbd5e1;
        }

        .btn-invoice--secondary:hover:not(:disabled) {
            background: #f8fafc;
            border-color: #94a3b8;
        }

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
    @php
        $pdfFilename = 'invoice-' . preg_replace('/[^A-Za-z0-9._-]+/', '-', $invoice['booking']['invoice_no'] ?? $invoice['booking']['display_id'] ?? 'booking') . '.pdf';
    @endphp

    <div class="invoice-toolbar no-print">
        <a href="{{ route('bookings.show', $invoice['booking']['id']) }}">&larr; {{ __('invoice.back_to_booking') }}</a>
        <div class="invoice-toolbar-actions">
            {{--<button type="button" class="btn-invoice btn-invoice--secondary" onclick="window.print()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                    <rect x="6" y="14" width="12" height="8"></rect>
                </svg>
                {{ __('invoice.print_invoice') }}
            </button>--}}
            <button
                type="button"
                class="btn-invoice btn-invoice--primary"
                id="download-invoice-pdf"
                data-filename="{{ $pdfFilename }}"
                data-generating-label="{{ __('invoice.generating_pdf') }}"
                data-default-label="{{ __('invoice.download_invoice') }}"
            >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span class="btn-label">{{ __('invoice.download_invoice') }}</span>
            </button>
        </div>
    </div>

    <div class="invoice-page">
        <x-invoice.template :data="$invoice" />
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('download-invoice-pdf').addEventListener('click', function () {
            const button = this;
            const invoiceElement = document.querySelector('.invoice');

            if (!invoiceElement || typeof html2pdf === 'undefined') {
                return;
            }

            const defaultLabel = button.dataset.defaultLabel;
            const generatingLabel = button.dataset.generatingLabel;
            const filename = button.dataset.filename || 'invoice.pdf';
            const label = button.querySelector('.btn-label');

            button.disabled = true;
            if (label) {
                label.textContent = generatingLabel;
            }

            html2pdf()
                .set({
                    margin: [8, 8, 8, 8],
                    filename: filename,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: {
                        scale: 2,
                        useCORS: true,
                        logging: false,
                        backgroundColor: '#ffffff',
                    },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: ['avoid-all', 'css', 'legacy'] },
                })
                .from(invoiceElement)
                .save()
                .finally(function () {
                    button.disabled = false;
                    if (label) {
                        label.textContent = defaultLabel;
                    }
                });
        });
    </script>
</body>
</html>
