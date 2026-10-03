@php
    $a = $address;
    $isSubmission = $payable instanceof App\Models\ManuscriptSubmission;
@endphp
<x-layouts.account title="Checkout" :heading="$purpose->label().' fee'" :sidebar="false">
    <div class="grid gap-8 2xl:grid-cols-[minmax(0,1fr)_18rem]">
        <form method="POST" action="{{ route('account.checkout.store') }}" x-data="{ country: @js(old('country_code', $a?->country_code ?? 'IN')) }">
            @csrf
            <input type="hidden" name="payable_type" value="{{ $payable->getMorphClass() }}">
            <input type="hidden" name="payable_id" value="{{ $payable->getKey() }}">
            <input type="hidden" name="purpose" value="{{ $purpose->value }}">
            <x-admin.panel title="Billing address" description="Printed on your invoice. All fees are inclusive of taxes.">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-form.input name="recipient_name" label="Name on invoice" :value="$a?->recipient_name ?? auth()->user()->name" required />
                    <x-form.input name="organization_name" label="Organisation / institution" :value="$a?->organization_name" />
                    <x-form.input name="address_line1" label="Address line 1" :value="$a?->address_line1" required class="md:col-span-2" />
                    <x-form.input name="address_line2" label="Address line 2" :value="$a?->address_line2" class="md:col-span-2" />
                    <x-form.field label="Country" name="country_code" required>
                        <select name="country_code" x-model="country" class="field-input">
                            @foreach ($countries as $code => $name)<option value="{{ $code }}">{{ $name }}</option>@endforeach
                        </select>
                    </x-form.field>
                    <x-form.billing-state :value="$a?->state" />
                    <x-form.input name="city" label="City" :value="$a?->city" required />
                    <x-form.input name="postal_code" label="Postal code" :value="$a?->postal_code" />
                    <x-form.phone label="Phone" :value="$a?->phone ?? auth()->user()->phone" />
                    <x-form.input name="tax_id_number" label="GST / VAT / Tax ID (optional)" :value="$a?->tax_id_number" maxlength="40" hint="Printed on your invoice, e.g. your GSTIN." />
                </div>
            </x-admin.panel>
            <x-button type="submit" variant="primary" icon="lock" class="mt-6">Continue to payment</x-button>
        </form>

        <aside class="order-first h-fit border border-border bg-card p-6 2xl:sticky 2xl:top-40 2xl:order-none">
            <p class="label-caps text-xs text-muted-foreground">Order summary</p>
            <p class="mt-2 font-display text-xl">{{ $purpose->label() }} fee</p>
            <p class="text-sm text-muted-foreground">{{ $isSubmission ? $payable->reference().' — '.Str::limit($payable->title, 60) : 'Plagiarism check: '.Str::limit($payable->title, 60) }}</p>
            <dl class="mt-5 space-y-2 text-base">
                <div class="flex justify-between border-t border-border pt-2 text-lg font-semibold"><dt>Total payable</dt><dd>{{ money($amount) }}</dd></div>
            </dl>
            <p class="mt-1 text-sm text-muted-foreground">Inclusive of all taxes. The tax breakup is shown on your invoice.</p>
            <p class="mt-5 text-xs text-muted-foreground">Payments are processed securely by Razorpay in {{ settings('payment.currency') }}.</p>
        </aside>
    </div>
</x-layouts.account>
