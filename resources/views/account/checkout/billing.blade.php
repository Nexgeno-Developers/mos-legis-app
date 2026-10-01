@php
    $a = $address;
    $isSubmission = $payable instanceof App\Models\ManuscriptSubmission;
@endphp
<x-layouts.account title="Checkout" :heading="$purpose->label().' fee'">
    <div class="grid gap-8 2xl:grid-cols-[minmax(0,1fr)_18rem]">
        <form method="POST" action="{{ route('account.checkout.store') }}" x-data="{ country: @js(old('country_code', $a?->country_code ?? 'IN')), taxType: @js(old('tax_id_type', $a?->tax_id_type?->value ?? 'none')) }">
            @csrf
            <input type="hidden" name="payable_type" value="{{ $payable->getMorphClass() }}">
            <input type="hidden" name="payable_id" value="{{ $payable->getKey() }}">
            <input type="hidden" name="purpose" value="{{ $purpose->value }}">
            <x-admin.panel title="Billing address" description="Printed on your invoice. Indian addresses are charged tax at the configured rate; international payers are zero-rated.">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-form.input name="recipient_name" label="Name on invoice" :value="$a?->recipient_name ?? auth()->user()->name" required />
                    <x-form.input name="organization_name" label="Organisation / institution" :value="$a?->organization_name" />
                    <x-form.input name="address_line1" label="Address line 1" :value="$a?->address_line1" required class="md:col-span-2" />
                    <x-form.input name="address_line2" label="Address line 2" :value="$a?->address_line2" class="md:col-span-2" />
                    <x-form.input name="city" label="City" :value="$a?->city" required />
                    <x-form.input name="state" label="State / region" :value="$a?->state" />
                    <x-form.input name="postal_code" label="Postal code" :value="$a?->postal_code" />
                    <x-form.field label="Country" name="country_code" required>
                        <select name="country_code" x-model="country" class="field-input">
                            @foreach ($countries as $code => $name)<option value="{{ $code }}">{{ $name }}</option>@endforeach
                        </select>
                    </x-form.field>
                    <x-form.phone label="Phone" :value="$a?->phone ?? auth()->user()->phone" />
                    <x-form.field label="Tax registration" name="tax_id_type" required>
                        <select name="tax_id_type" x-model="taxType" class="field-input">
                            @foreach (['none' => 'None', 'gst' => 'GST (India)', 'vat' => 'VAT'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </x-form.field>
                    <div x-show="taxType !== 'none'" x-cloak class="md:col-span-2"><x-form.input name="tax_id_number" label="Tax registration number" :value="$a?->tax_id_number" /></div>
                </div>
            </x-admin.panel>
            <x-button type="submit" variant="primary" icon="lock" class="mt-6">Continue to payment</x-button>
        </form>

        <aside class="order-first h-fit border border-border bg-card p-6 2xl:sticky 2xl:top-40 2xl:order-none" x-data="{ get taxed() { return document.querySelector('[name=country_code]')?.value === 'IN' } }">
            <p class="label-caps text-xs text-muted-foreground">Order summary</p>
            <p class="mt-2 font-display text-xl">{{ $purpose->label() }} fee</p>
            <p class="text-sm text-muted-foreground">{{ $isSubmission ? $payable->reference().' — '.Str::limit($payable->title, 60) : 'Plagiarism check: '.Str::limit($payable->title, 60) }}</p>
            <dl class="mt-5 space-y-2 text-base">
                <div class="flex justify-between"><dt>Fee</dt><dd>{{ money($amount) }}</dd></div>
                <div class="flex justify-between text-muted-foreground"><dt>Tax ({{ rtrim(rtrim(number_format($taxRate, 2), '0'), '.') }}% for India)</dt><dd>{{ money(round($amount * $taxRate / 100, 2)) }}</dd></div>
                <div class="flex justify-between border-t border-border pt-2 font-semibold"><dt>Total (Indian address)</dt><dd>{{ money(round($amount * (1 + $taxRate / 100), 2)) }}</dd></div>
                <div class="flex justify-between text-sm text-muted-foreground"><dt>Total (international)</dt><dd>{{ money($amount) }}</dd></div>
            </dl>
            <p class="mt-5 text-xs text-muted-foreground">Payments are processed securely by Razorpay in {{ settings('payment.currency') }}.</p>
        </aside>
    </div>
</x-layouts.account>
