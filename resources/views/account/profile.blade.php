@php
    $profile = $user->authorProfile;
    $a = $user->address;
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->take(2)->implode('');
@endphp
<x-layouts.account title="Profile" intro="Your author profile pre-fills submissions and appears on certificates.">
    <div class="space-y-8">
        {{-- Author profile + password --}}
        <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf @method('PUT')

            <x-admin.panel title="Author profile" description="Author category is required before you submit a manuscript.">
                <div class="grid gap-6 xl:grid-cols-[9rem_minmax(0,1fr)] xl:gap-8">
                    {{-- Photo --}}
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 xl:flex-col xl:items-start" x-data="{ preview: null }">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-full border border-border bg-secondary xl:h-28 xl:w-28">
                            <template x-if="preview"><img :src="preview" alt="" class="h-full w-full object-cover"></template>
                            <template x-if="!preview">
                                @if ($profile?->profile_picture)
                                    <img src="{{ Storage::disk('public')->url($profile->profile_picture) }}" alt="" class="h-full w-full object-cover">
                                @else
                                    <span class="flex h-full w-full items-center justify-center bg-primary font-display text-4xl text-primary-foreground">{{ $initials }}</span>
                                @endif
                            </template>
                        </div>
                        <label class="cursor-pointer text-sm font-semibold text-primary hover:underline">
                            {{ $profile?->profile_picture ? 'Change photo' : 'Upload photo' }}
                            <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null">
                        </label>
                        <p class="text-xs text-muted-foreground">JPG, PNG or WebP, up to 2 MB.</p>
                        @error('profile_picture')<p class="text-sm text-destructive">{{ $message }}</p>@enderror
                    </div>

                    {{-- Fields --}}
                    <div class="grid content-start gap-5 sm:grid-cols-2">
                        <x-form.input name="name" label="Full name" :value="$user->name" required autocomplete="name" />
                        <x-form.field label="Email" hint="Contact the editorial office to change it.">
                            <input type="email" value="{{ $user->email }}" class="field-input" disabled>
                        </x-form.field>
                        <x-form.phone :value="$user->phone" />
                        <x-form.select name="author_category_id" label="Author category" :options="$authorCategories" :value="$profile?->author_category_id" placeholder="Select your category" required />
                        <x-form.input name="institution" label="Institution / organisation" :value="$profile?->institution" required autocomplete="organization" />
                        <x-form.input name="country" label="Country" :value="$profile?->country" autocomplete="country-name" />
                        <x-orcid-connect :orcid="$profile?->orcid" locked class="sm:col-span-2" />
                        <x-form.textarea name="bio" label="Professional biography" :value="$profile?->bio" rows="4" class="sm:col-span-2" />
                    </div>
                </div>
            </x-admin.panel>

            <x-admin.panel :title="$user->password ? 'Change password' : 'Set a password'"
                :description="$user->password ? 'Leave blank to keep your current password.' : 'You signed up with Google. Set a password to also sign in with your email.'">
                <div @class(["grid gap-5 md:grid-cols-2", "xl:grid-cols-3" => $user->password])>
                    @if ($user->password)
                        <x-form.input name="current_password" type="password" label="Current password" autocomplete="current-password" />
                    @endif
                    <x-form.input name="password" type="password" label="New password" autocomplete="new-password" hint="At least 8 characters." />
                    <x-form.input name="password_confirmation" type="password" label="Confirm new password" autocomplete="new-password" />
                </div>
            </x-admin.panel>

            <div class="flex justify-end">
                <x-button type="submit" variant="primary" icon="save">Save profile</x-button>
            </div>
        </form>

        {{-- Billing address --}}
        <form method="POST" action="{{ route('account.profile.address') }}" x-data="{ taxType: @js(old('tax_id_type', $a?->tax_id_type?->value ?? 'none')) }" class="space-y-8">
            @csrf @method('PUT')
            <x-admin.panel title="Billing address" description="Printed on your invoices. Each payment keeps its own copy, so editing this never changes past invoices.">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-form.input name="recipient_name" label="Name on invoice" :value="$a?->recipient_name ?? $user->name" required />
                    <x-form.input name="organization_name" label="Organisation (optional)" :value="$a?->organization_name" />
                    <x-form.input name="address_line1" label="Address line 1" :value="$a?->address_line1" required class="md:col-span-2" autocomplete="address-line1" />
                    <x-form.input name="address_line2" label="Address line 2" :value="$a?->address_line2" class="md:col-span-2" autocomplete="address-line2" />
                    <x-form.input name="city" label="City" :value="$a?->city" required autocomplete="address-level2" />
                    <x-form.input name="state" label="State / region" :value="$a?->state" autocomplete="address-level1" />
                    <x-form.input name="postal_code" label="Postal code" :value="$a?->postal_code" autocomplete="postal-code" />
                    <x-form.select name="country_code" label="Country" :options="$countries" :value="$a?->country_code ?? 'IN'" required />
                    <x-form.phone id="billing_phone" label="Billing phone" :value="$a?->phone" />
                    <x-form.field label="Tax registration" name="tax_id_type">
                        <select name="tax_id_type" id="tax_id_type" x-model="taxType" class="field-input">
                            @foreach (['none' => 'None', 'gst' => 'GST (India)', 'vat' => 'VAT'] as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </x-form.field>
                    <div x-show="taxType !== 'none'" x-cloak class="md:col-span-2">
                        <x-form.input name="tax_id_number" label="Tax registration number" :value="$a?->tax_id_number" />
                    </div>
                </div>
                <p class="mt-5 text-sm text-muted-foreground">Indian billing addresses are charged tax at {{ rtrim(rtrim(number_format(settings()->float('payment.tax_rate_percent'), 2), '0'), '.') }}%; international addresses are zero-rated.</p>
            </x-admin.panel>
            <div class="flex justify-end">
                <x-button type="submit" icon="save">Save billing address</x-button>
            </div>
        </form>
    </div>
</x-layouts.account>
