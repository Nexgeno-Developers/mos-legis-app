@php
    $statusUrl = route('account.payments.status', $payment);
    $lastFailed = $payment->payment_status === App\Enums\PaymentStatus::Failed;
@endphp
<x-layouts.account title="Payment" :heading="'Pay '.money($payment->total_amount)" :sidebar="false" narrow>
    <div @if ($gateway === 'razorpay') x-data="razorpayCheckout" @endif>
        @if ($lastFailed)
            <div class="mb-6 flex gap-3 border border-warning/40 bg-card p-4 text-sm" role="status">
                <x-icon name="circle-alert" class="mt-0.5 h-5 w-5 text-warning" />
                <p>Your last attempt didn't go through{{ $payment->remarks ? ' ('.rtrim($payment->remarks, '.').')' : '' }}. You can try again. If money was taken from your account, the payment will be confirmed automatically, so check Payments &amp; Invoices before paying again.</p>
            </div>
        @endif

        <div class="border border-border bg-card p-6 md:p-8">
            <p class="label-caps text-xs text-muted-foreground">You are paying for</p>
            <p class="mt-2 font-display text-xl">{{ $payment->payment_purpose->label() }} fee</p>
            <p class="text-sm text-muted-foreground">{{ $item }}</p>

            <dl class="mt-6 space-y-2">
                @if (($b = $breakdown ?? null) && $b['surcharge'] > 0)
                    <div class="flex justify-between"><dt>Publication fee</dt><dd>{{ money($b['base']) }}</dd></div>
                    <div class="flex justify-between"><dt>Co-author surcharge <span class="text-sm text-muted-foreground">({{ $b['co_authors'] }} co-author{{ $b['co_authors'] === 1 ? '' : 's' }})</span></dt><dd>{{ money($b['surcharge']) }}</dd></div>
                @else
                    <div class="flex justify-between"><dt>{{ $payment->payment_purpose->label() }} fee</dt><dd>{{ money($payment->total_amount) }}</dd></div>
                @endif
                <div class="flex justify-between border-t border-border pt-3 text-lg font-semibold"><dt>Total payable</dt><dd>{{ money($payment->total_amount) }}</dd></div>
            </dl>
            <p class="mt-1 text-sm text-muted-foreground">Inclusive of all taxes. The tax breakup is shown on your invoice.</p>
            <p class="mt-4 text-sm text-muted-foreground">Billed to {{ $payment->billing_details['name'] ?? '' }}, {{ $payment->billing_details['city'] ?? '' }} ({{ $payment->billing_country_code }}).
                <a href="{{ $checkoutUrl }}" class="text-primary hover:underline">Change</a></p>

            @if ($gateway === 'razorpay')
                <form x-ref="callback" method="POST" action="{{ route('payments.razorpay.callback') }}" class="hidden">
                    @csrf
                    <input type="hidden" name="razorpay_order_id"><input type="hidden" name="razorpay_payment_id"><input type="hidden" name="razorpay_signature">
                </form>

                <div x-show="cancelled" x-cloak class="mt-6 flex gap-3 border border-border bg-secondary p-3 text-sm" role="status">
                    <x-icon name="info" class="mt-0.5 h-4 w-4" />
                    <p>Payment cancelled. You have not been charged, so you can pay whenever you are ready.</p>
                </div>
                <div x-show="unavailable" x-cloak class="mt-6 flex gap-3 border border-destructive/40 p-3 text-sm text-destructive" role="alert">
                    <x-icon name="circle-alert" class="mt-0.5 h-4 w-4" />
                    <p>The payment window could not be loaded. Check your internet connection and reload this page.</p>
                </div>

                <x-button variant="primary" icon="lock" class="mt-8 w-full md:w-auto" x-on:click.prevent="open()" x-bind:disabled="busy">
                    Pay {{ money($payment->total_amount) }} securely
                </x-button>
            @else
                <div class="mt-8 border-l-2 border-warning bg-secondary px-4 py-3 text-sm">
                    Razorpay keys are not configured, so this environment uses a simulated payment.
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <form method="POST" action="{{ route('account.payments.simulate', $payment) }}">
                        @csrf
                        <x-button type="submit" variant="primary" icon="check">Simulate successful payment</x-button>
                    </form>
                    <form method="POST" action="{{ route('account.payments.simulate', $payment) }}">
                        @csrf
                        <input type="hidden" name="outcome" value="failed">
                        <x-button type="submit" icon="x">Simulate failed payment</x-button>
                    </form>
                </div>
            @endif

            <p class="mt-6 flex items-center gap-2 border-t border-border pt-4 text-xs text-muted-foreground">
                <x-icon name="shield-check" class="h-4 w-4 text-success" />
                UPI, cards, netbanking and wallets. Payments are processed securely by Razorpay; we never see your card or bank details.
            </p>
        </div>

        {{-- Shown while the payment is handed over for confirmation. --}}
        <div x-show="busy" x-cloak class="fixed inset-0 z-[70] grid place-items-center bg-background/90 px-6" role="status" aria-live="polite">
            <div class="text-center">
                <x-icon name="loader-circle" class="mx-auto h-10 w-10 animate-spin text-primary" />
                <p class="mt-4 font-display text-xl">Confirming your payment…</p>
                <p class="mt-1 text-sm text-muted-foreground">Please don't close this page or press back.</p>
            </div>
        </div>
    </div>

    @if ($gateway === 'razorpay')
        @push('scripts')
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <script>
                document.addEventListener('alpine:init', () => {
                    Alpine.data('razorpayCheckout', () => ({
                        busy: false,
                        cancelled: false,
                        unavailable: false,
                        attempted: false,
                        checkout: null,

                        open() {
                            if (typeof Razorpay === 'undefined') {
                                this.unavailable = true;
                                return;
                            }
                            this.cancelled = false;
                            this.checkout ??= this.build();
                            this.checkout.open();
                        },

                        build() {
                            const options = @js($options);
                            const statusUrl = @js($statusUrl);

                            // Success in the widget: verify the signature on the server, then show the outcome.
                            options.handler = (response) => {
                                this.busy = true;
                                const form = this.$refs.callback;
                                ['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature'].forEach((field) => form.elements[field].value = response[field]);
                                form.submit();
                            };
                            options.modal = {
                                confirm_close: true,
                                ondismiss: () => {
                                    // Closed after an attempt: the bank may still confirm it, so let the status page check.
                                    if (this.attempted) {
                                        this.busy = true;
                                        window.location.href = statusUrl;
                                    } else {
                                        this.cancelled = true;
                                    }
                                },
                            };

                            const checkout = new Razorpay(options);
                            // A "failed" in the widget is not final (UPI and some banks confirm seconds later):
                            // hand over to the status page, which checks with Razorpay before showing the result.
                            checkout.on('payment.failed', () => {
                                this.attempted = true;
                                this.busy = true;
                                try { checkout.close(); } catch (e) {}
                                window.location.href = statusUrl;
                            });

                            return checkout;
                        },
                    }));
                });
            </script>
        @endpush
    @endif
</x-layouts.account>
