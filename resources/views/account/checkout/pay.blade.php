<x-layouts.account title="Payment" :heading="'Pay '.money($payment->total_amount)" :sidebar="false">
    <div class="max-w-2xl border border-border bg-card p-8">
        <dl class="space-y-2">
            <div class="flex justify-between"><dt>{{ $payment->payment_purpose->label() }} fee</dt><dd>{{ money($payment->amount) }}</dd></div>
            @if ($payment->hasTax())<div class="flex justify-between text-muted-foreground"><dt>Tax ({{ (float) $payment->tax_rate }}%)</dt><dd>{{ money($payment->tax_amount) }}</dd></div>@endif
            <div class="flex justify-between border-t border-border pt-2 text-lg font-semibold"><dt>Total</dt><dd>{{ money($payment->total_amount) }}</dd></div>
        </dl>
        <p class="mt-3 text-sm text-muted-foreground">Billed to {{ $payment->billing_details['name'] ?? '' }}, {{ $payment->billing_details['city'] ?? '' }} ({{ $payment->billing_country_code }}).
            <a href="{{ url()->previous() }}" class="text-primary hover:underline">Change</a></p>

        @if ($gateway === 'razorpay')
            <form id="razorpay-callback" method="POST" action="{{ route('payments.razorpay.callback') }}" class="hidden">
                @csrf
                <input type="hidden" name="razorpay_order_id"><input type="hidden" name="razorpay_payment_id"><input type="hidden" name="razorpay_signature">
            </form>
            <x-button id="razorpay-pay" variant="primary" icon="credit-card" class="mt-8">Pay with Razorpay</x-button>
            @push('scripts')
                <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
                <script>
                    (function () {
                        const options = @js($options);
                        options.handler = function (response) {
                            const form = document.getElementById('razorpay-callback');
                            ['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'].forEach((field) => form.elements[field].value = response[field]);
                            form.submit();
                        };
                        options.modal = { ondismiss: function () {} };
                        const checkout = new Razorpay(options);
                        document.getElementById('razorpay-pay').addEventListener('click', (e) => { e.preventDefault(); checkout.open(); });
                    })();
                </script>
            @endpush
        @else
            <div class="mt-8 border-l-2 border-warning bg-secondary px-4 py-3 text-sm">
                Razorpay keys are not configured, so this environment uses a simulated payment.
            </div>
            <form method="POST" action="{{ route('account.payments.simulate', $payment) }}" class="mt-4">
                @csrf
                <x-button type="submit" variant="primary" icon="check">Simulate successful payment</x-button>
            </form>
        @endif
    </div>
</x-layouts.account>
