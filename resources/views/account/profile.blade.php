@php
    $profile = $user->authorProfile;
    $a = $user->address;
@endphp
<x-layouts.account title="Profile" intro="Your author profile is used to pre-fill submissions and on certificates.">
    <div class="grid gap-8 lg:grid-cols-2">
        <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf @method('PUT')
            <x-admin.panel title="Author profile">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-form.input name="name" label="Full name" :value="$user->name" required />
                    <x-form.field label="Email" hint="Contact the editorial office to change your email.">
                        <input type="email" value="{{ $user->email }}" class="field-input" disabled>
                    </x-form.field>
                    <x-form.input name="phone" label="Mobile number" :value="$user->phone" />
                    <x-form.select name="author_category_id" label="Author category" :options="$authorCategories" :value="$profile?->author_category_id" placeholder="Select" required />
                    <x-form.input name="institution" label="Institution / organisation" :value="$profile?->institution" />
                    <x-form.input name="country" label="Country" :value="$profile?->country" />
                    <x-form.input name="orcid" label="ORCID iD" placeholder="0000-0000-0000-0000" :value="$profile?->orcid" />
                    <x-form.image name="profile_picture" label="Profile picture" :current="$profile?->profile_picture" />
                    <x-form.textarea name="bio" label="Professional biography" :value="$profile?->bio" rows="4" class="md:col-span-2" />
                </div>
            </x-admin.panel>
            <x-admin.panel :title="$user->password ? 'Change password' : 'Set a password'" :description="$user->password ? 'Leave blank to keep your current password.' : 'You signed up with Google or ORCID. Set a password to also sign in with email.'">
                <div class="grid gap-5 md:grid-cols-2">
                    @if ($user->password)<x-form.input name="current_password" type="password" label="Current password" class="md:col-span-2" />@endif
                    <x-form.input name="password" type="password" label="New password" />
                    <x-form.input name="password_confirmation" type="password" label="Confirm password" />
                </div>
            </x-admin.panel>
            <x-button type="submit" variant="primary" icon="save">Save profile</x-button>
        </form>

        <form method="POST" action="{{ route('account.profile.address') }}" class="space-y-8" x-data="{ taxType: @js(old('tax_id_type', $a?->tax_id_type?->value ?? 'none')) }">
            @csrf @method('PUT')
            <x-admin.panel title="Billing address" description="Used for invoices. Each payment keeps its own copy, so editing this never changes past invoices.">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-form.input name="recipient_name" label="Name on invoice" :value="$a?->recipient_name ?? $user->name" required />
                    <x-form.input name="organization_name" label="Organisation" :value="$a?->organization_name" />
                    <x-form.input name="address_line1" label="Address line 1" :value="$a?->address_line1" required class="md:col-span-2" />
                    <x-form.input name="address_line2" label="Address line 2" :value="$a?->address_line2" class="md:col-span-2" />
                    <x-form.input name="city" label="City" :value="$a?->city" required />
                    <x-form.input name="state" label="State / region" :value="$a?->state" />
                    <x-form.input name="postal_code" label="Postal code" :value="$a?->postal_code" />
                    <x-form.select name="country_code" label="Country" :options="$countries" :value="$a?->country_code ?? 'IN'" required />
                    <x-form.input name="phone" label="Phone" :value="$a?->phone" />
                    <x-form.field label="Tax registration" name="tax_id_type">
                        <select name="tax_id_type" x-model="taxType" class="field-input">
                            @foreach (['none' => 'None', 'gst' => 'GST (India)', 'vat' => 'VAT'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </x-form.field>
                    <div x-show="taxType !== 'none'" x-cloak class="md:col-span-2"><x-form.input name="tax_id_number" label="Tax registration number" :value="$a?->tax_id_number" /></div>
                </div>
            </x-admin.panel>
            <x-button type="submit" icon="save">Save billing address</x-button>
        </form>
    </div>
</x-layouts.account>
