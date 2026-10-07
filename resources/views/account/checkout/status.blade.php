@php
    use App\Enums\PaymentPurpose;

    $statusUrl = route('account.payments.status', $payment);
    $heading = match ($view) {
        'success' => 'Payment successful',
        'processing' => 'Confirming your payment',
        'pending' => 'Your payment is being confirmed',
        default => 'Payment not completed',
    };
    $continueLabel = $payment->payment_purpose === PaymentPurpose::PlagiarismCheck ? 'View plagiarism check' : 'View manuscript';
    $nextStep = match ($payment->payment_purpose) {
        PaymentPurpose::Prescreening => 'Your manuscript now goes through plagiarism pre-screening and then peer review. We will email you at each step.',
        PaymentPurpose::Publication => 'Your manuscript is being published. Your publication certificate will be available on the manuscript page.',
        PaymentPurpose::PlagiarismCheck => 'Your plagiarism check has started. The report will be ready on the check page shortly.',
    };
@endphp
<x-layouts.account title="Payment status" :heading="$heading" :sidebar="false" narrow>
    <div>
        @if ($view === 'success')
            <div class="border border-border bg-card p-6 md:p-8">
                <div class="flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-success/10 text-success"><x-icon name="circle-check" class="h-7 w-7" /></span>
                    <div>
                        <p class="font-display text-2xl">{{ money($payment->total_amount) }} received</p>
                        <p class="mt-1 text-sm text-muted-foreground">Thank you. A receipt has been emailed to {{ $payment->user->email }}.</p>
                    </div>
                </div>

                <dl class="mt-6 divide-y divide-border border-y border-border text-sm">
                    @foreach (array_filter([
                        'Paid for' => $item,
                        'Purpose' => $payment->payment_purpose->label().' fee',
                        'Amount' => money($payment->total_amount).' (inclusive of all taxes)',
                        'Invoice number' => $payment->invoice_number,
                        'Payment ID' => $payment->payment_id,
                        'Paid with' => $payment->payment_details ?? ($payment->payment_method ? Str::upper($payment->payment_method) : null),
                        'Date' => $payment->paid_at?->format('d M Y, h:i A'),
                    ]) as $label => $value)
                        <div class="grid grid-cols-[9rem_1fr] gap-3 py-2.5"><dt class="text-muted-foreground">{{ $label }}</dt><dd class="break-words">{{ $value }}</dd></div>
                    @endforeach
                </dl>

                <div class="mt-6 flex gap-3 border-l-2 border-gold bg-secondary px-4 py-3 text-sm">
                    <x-icon name="info" class="mt-0.5 h-4 w-4" />
                    <p><span class="font-semibold">What happens next:</span> {{ $nextStep }}</p>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button :href="$returnUrl" variant="primary" icon="arrow-right">{{ $continueLabel }}</x-button>
                    <x-button :href="route('account.payments.invoice', $payment)" icon="download">Download invoice</x-button>
                </div>
            </div>

        @elseif ($view === 'processing')
            <div class="border border-border bg-card p-6 text-center md:p-10" role="status" aria-live="polite"
                x-data="{
                    started: Date.now(), elapsed: 0, failedChecks: 0,
                    init() { this.poll(); setInterval(() => this.elapsed = Date.now() - this.started, 500); },
                    async poll() {
                        try {
                            const response = await fetch(@js(route('account.payments.check', $payment)), { headers: { Accept: 'application/json' } });
                            const { state } = await response.json();
                            if (state === 'paid') return window.location.replace(@js($statusUrl));
                            this.failedChecks = ['failed', 'none'].includes(state) ? this.failedChecks + 1 : 0;
                        } catch (e) {}
                        const waited = Date.now() - this.started;
                        // A clear failure is shown after ~15s of consistent answers; anything in progress gets a full minute.
                        if ((this.failedChecks >= 4 && waited >= 15000) || waited >= 60000) {
                            return window.location.replace(@js($statusUrl.'?final=1'));
                        }
                        setTimeout(() => this.poll(), 3000);
                    },
                }">
                <x-icon name="loader-circle" class="mx-auto h-12 w-12 animate-spin text-primary" />
                <p class="mt-5 font-display text-2xl">Checking with your bank…</p>
                <p class="mx-auto mt-2 max-w-md text-sm text-muted-foreground">
                    Some payments, especially UPI and netbanking, take a few seconds to be confirmed by the bank.
                    Please don't close this page, press back or pay again.
                </p>
                <div class="mx-auto mt-6 h-1 max-w-sm overflow-hidden bg-secondary">
                    <div class="h-full bg-primary transition-all duration-500" x-bind:style="`width: ${Math.min(100, elapsed / 600)}%`"></div>
                </div>
                <p class="mt-4 text-xs text-muted-foreground">{{ money($payment->total_amount) }} · {{ $payment->payment_purpose->label() }} fee</p>
                <noscript><p class="mt-4 text-sm"><a href="{{ $statusUrl }}?final=1" class="text-primary hover:underline">Check payment status</a></p></noscript>
            </div>

        @elseif ($view === 'pending')
            <div class="border border-border bg-card p-6 md:p-8">
                <div class="flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-warning/10 text-warning"><x-icon name="hourglass" class="h-6 w-6" /></span>
                    <div>
                        <p class="font-display text-2xl">We're still waiting for your bank</p>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Your payment of {{ money($payment->total_amount) }} has not been confirmed yet. This usually takes a few minutes.
                            <strong class="text-foreground">If you completed the payment in your bank or UPI app, please don't pay again.</strong> We will update your account and email you at {{ $payment->user->email }} as soon as it is confirmed.
                            If the payment fails, any amount debited is refunded by your bank.
                        </p>
                    </div>
                </div>
                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button :href="$statusUrl.'?final=1'" variant="primary" icon="refresh-cw">Check again</x-button>
                    <x-button :href="$returnUrl" icon="arrow-left">{{ $continueLabel }}</x-button>
                </div>
            </div>

        @else
            <div class="border border-border bg-card p-6 md:p-8">
                <div class="flex items-start gap-4">
                    <span class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-destructive/10 text-destructive"><x-icon name="circle-x" class="h-7 w-7" /></span>
                    <div>
                        <p class="font-display text-2xl">Your payment didn't go through</p>
                        <p class="mt-1 text-sm text-muted-foreground">{{ $payment->remarks ? rtrim($payment->remarks, '.').'.' : 'The payment was cancelled or declined.' }} You can try again.</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-3 border-l-2 border-gold bg-secondary px-4 py-3 text-sm">
                    <x-icon name="info" class="mt-0.5 h-4 w-4" />
                    <p>If money was debited from your account, don't worry: it is either confirmed here automatically within a few minutes (we'll email you) or refunded by your bank within 5–7 working days.</p>
                </div>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button :href="route('account.payments.pay', $payment)" variant="primary" icon="refresh-cw">Try again</x-button>
                    <x-button :href="$checkoutUrl" icon="pencil">Change billing details</x-button>
                    <x-button :href="$returnUrl" variant="ghost" icon="arrow-left">{{ $continueLabel }}</x-button>
                </div>
                <p class="mt-6 border-t border-border pt-4 text-xs text-muted-foreground">
                    Need help? <a href="{{ page_url('contact') }}" class="text-primary hover:underline">Contact us</a> and mention reference
                    <span class="font-mono">{{ $payment->gateway_order_id ?? 'PAY-'.$payment->id }}</span>.
                </p>
            </div>
        @endif
    </div>
</x-layouts.account>
