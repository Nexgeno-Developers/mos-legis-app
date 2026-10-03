@php
    $editing = $user->exists;
    $profile = $user->authorProfile;
    $address = $user->address;
    $currentRole = old('role', $user->roles->first()?->name ?? 'author');
    $extraPermissions = old('permissions', $user->exists ? $user->permissions->pluck('name')->all() : []);
    $canGrantPermissions = auth()->user()->isSuperadmin();
@endphp
<x-layouts.admin :title="$editing ? 'Edit user' : 'Add user'">
    <x-admin.heading :title="$editing ? 'Edit user: '.$user->name : 'Add user'" description="Account details apply to everyone; the cards below change with the role and hold that role’s own data.">
        <x-slot:actions>
            <x-button :href="route('admin.users.index')" icon="arrow-left">Back to users</x-button>
        </x-slot:actions>
    </x-admin.heading>

    <form method="POST" action="{{ $editing ? route('admin.users.update', $user) : route('admin.users.store') }}" enctype="multipart/form-data"
        x-data="{ role: @js($currentRole), rolePermissions: @js($rolePermissions), country: @js(old('address.country_code', $address?->country_code ?? App\Support\PhoneNumbers::defaultCountry())) }" class="mx-auto mt-8 max-w-4xl space-y-8">
        @csrf
        @if ($editing) @method('PUT') @endif

        <x-admin.panel title="Account" description="Sign-in details and contact information.">
            <div class="grid gap-5 md:grid-cols-2">
                <x-form.input name="name" label="Name" :value="$user->name" required />
                <x-form.input name="email" type="email" label="Email" :value="$user->email" required />
                <x-form.phone label="Phone" :value="$user->phone" />
                <x-form.select name="status" label="Status" :options="App\Enums\RecordStatus::options()" :value="$user->status ?? 'Active'" required />
                <x-form.input name="password" type="password" :label="$editing ? 'New password' : 'Password'" :required="! $editing"
                    :hint="$editing ? 'Leave blank to keep the current password.' : 'At least 8 characters.'" autocomplete="new-password" />
                <x-form.input name="password_confirmation" type="password" label="Confirm password" autocomplete="new-password" />
            </div>
        </x-admin.panel>

        <x-admin.panel title="Role" description="Decides what the person can do. The cards below update to match.">
            <div class="grid gap-5 md:grid-cols-2 md:items-start">
                <x-form.field label="Role" name="role" required>
                    <select name="role" id="role" x-model="role" class="field-input" required>
                        @foreach ($roles as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </x-form.field>
                <div class="border-l-2 border-gold/60 bg-secondary px-4 py-3 text-sm text-muted-foreground md:mt-6">
                    <p x-show="role === 'author'">Author: signs in on the website to submit manuscripts, pay fees and download certificates.</p>
                    <p x-show="role === 'reviewer'" x-cloak>Reviewer: signs in to the console and reviews manuscripts in their assigned content categories.</p>
                    <p x-show="role === 'superadmin'" x-cloak>Superadmin: full access to every module of the console.</p>
                    <p x-show="! ['author', 'reviewer', 'superadmin'].includes(role)" x-cloak>Staff role: console access as defined under Roles &amp; Permissions.</p>
                </div>
            </div>
        </x-admin.panel>

        {{-- ===================== Author ===================== --}}
        <div x-show="role === 'author'" x-cloak class="space-y-8">
            <x-admin.panel title="Author profile" description="Pre-fills the author’s submissions and appears on certificates.">
                <div class="grid gap-6 lg:grid-cols-[9rem_minmax(0,1fr)] lg:gap-8">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 lg:flex-col lg:items-start" x-data="{ preview: null, remove: false }">
                        <div class="h-24 w-24 shrink-0 overflow-hidden rounded-full border border-border bg-secondary">
                            <template x-if="preview"><img :src="preview" alt="" class="h-full w-full object-cover"></template>
                            <template x-if="! preview">
                                @if ($profile?->profile_picture)
                                    <img src="{{ Storage::disk('public')->url($profile->profile_picture) }}" alt="" class="h-full w-full object-cover" :class="remove && 'opacity-30'">
                                @else
                                    <span class="flex h-full w-full items-center justify-center bg-primary font-display text-3xl text-primary-foreground">{{ $editing ? Str::upper(Str::substr($user->name, 0, 1)) : '?' }}</span>
                                @endif
                            </template>
                        </div>
                        <label class="cursor-pointer text-sm font-semibold text-primary hover:underline">
                            {{ $profile?->profile_picture ? 'Change photo' : 'Upload photo' }}
                            <input type="file" name="profile_picture" accept="image/jpeg,image/png,image/webp" class="sr-only"
                                @change="const f = $event.target.files[0]; preview = f ? URL.createObjectURL(f) : null; remove = false">
                        </label>
                        @if ($profile?->profile_picture)
                            <label class="flex items-center gap-2 text-sm text-muted-foreground">
                                <input type="hidden" name="remove_profile_picture" value="0">
                                <input type="checkbox" name="remove_profile_picture" value="1" x-model="remove" class="h-4 w-4 accent-primary"> Remove photo
                            </label>
                        @endif
                        <p class="text-xs text-muted-foreground">JPG, PNG or WebP, up to 2 MB.</p>
                        @error('profile_picture')<p class="text-sm text-destructive">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid content-start gap-5 md:grid-cols-2">
                        <x-form.select name="author_category_id" label="Author category" :options="$authorCategories" :value="$profile?->author_category_id"
                            placeholder="Select a category" required hint="Decides the author’s publication fee." />
                        <x-form.input name="institution" label="Institution / organisation" :value="$profile?->institution" />
                        <x-form.input name="country" label="Country" :value="$profile?->country" />
                        <x-form.input name="orcid" label="ORCID iD" :value="$profile?->orcid" placeholder="0000-0000-0000-0000"
                            hint="Authors can’t change their ORCID iD themselves — correct it here if needed." />
                        <x-form.textarea name="bio" label="Professional biography" :value="$profile?->bio" rows="4" class="md:col-span-2" />
                    </div>
                </div>
            </x-admin.panel>

            <x-admin.panel title="Billing address" description="Used on invoices and receipts. Optional — but once any field is filled in, the starred fields are needed.">
                <div class="grid gap-5 md:grid-cols-2">
                    <x-form.input name="address[recipient_name]" label="Recipient name *" :value="$address?->recipient_name" />
                    <x-form.input name="address[organization_name]" label="Organisation" :value="$address?->organization_name" />
                    <x-form.input name="address[address_line1]" label="Address line 1 *" :value="$address?->address_line1" />
                    <x-form.input name="address[address_line2]" label="Address line 2" :value="$address?->address_line2" />
                    <x-form.select name="address[country_code]" label="Country *" :options="$countries" :value="$address?->country_code ?? App\Support\PhoneNumbers::defaultCountry()" x-model="country" />
                    <x-form.billing-state name="address[state]" label="State / province" :value="$address?->state" :required="false" />
                    <x-form.input name="address[city]" label="City *" :value="$address?->city" />
                    <x-form.input name="address[postal_code]" label="Postal code" :value="$address?->postal_code" />
                    <x-form.phone name="address[phone]" label="Billing phone" :value="$address?->phone" />
                    <x-form.input name="address[tax_id_number]" label="GST / VAT / Tax ID" :value="$address?->tax_id_number" maxlength="40" />
                </div>
            </x-admin.panel>
        </div>

        {{-- ===================== Reviewer ===================== --}}
        <div x-show="role === 'reviewer'" x-cloak>
            <x-admin.panel title="Review assignment" description="Manuscripts in these content categories are auto-assigned to this reviewer.">
                <x-form.multi-select name="content_category_ids" label="Content categories" :options="$contentCategories" required
                    :value="$user->reviewerContentCategories?->pluck('id')->all() ?? []" placeholder="Search and select content categories…" />
            </x-admin.panel>
        </div>

        {{-- ===================== Staff roles: extra permissions ===================== --}}
        @if ($canGrantPermissions)
            <div x-show="! ['author', 'superadmin'].includes(role)" x-cloak>
                <x-admin.panel title="Additional permissions" description="Grant this person access beyond their role. Ticked and greyed-out items already come with the role.">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($modules as $module => $definition)
                            <fieldset class="border border-border p-4">
                                <legend class="label-caps px-1 text-xs text-foreground">{{ $definition['label'] }}</legend>
                                <div class="mt-1 space-y-2">
                                    @foreach ($definition['abilities'] as $ability)
                                        @php $permission = "{$module}.{$ability}"; @endphp
                                        <label class="flex items-center gap-2 text-base" :class="(rolePermissions[role] || []).includes(@js($permission)) && 'text-muted-foreground'">
                                            <input type="checkbox" name="permissions[]" value="{{ $permission }}" data-extra="{{ in_array($permission, $extraPermissions, true) ? 1 : 0 }}"
                                                :disabled="(rolePermissions[role] || []).includes(@js($permission))"
                                                x-effect="$el.checked = (rolePermissions[role] || []).includes(@js($permission)) || $el.dataset.extra === '1'"
                                                @change="$el.dataset.extra = $el.checked ? '1' : '0'"
                                                class="h-4 w-4 accent-primary">
                                            {{ Str::of($ability)->replace('-', ' ')->ucfirst() }}
                                            <span x-show="(rolePermissions[role] || []).includes(@js($permission))" class="text-xs">(role)</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach
                    </div>
                    @error('permissions.*')<p class="mt-3 text-sm text-destructive">{{ $message }}</p>@enderror
                </x-admin.panel>
            </div>
        @endif

        {{-- ===================== Superadmin ===================== --}}
        <div x-show="role === 'superadmin'" x-cloak>
            <x-admin.panel title="Superadmin access">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center bg-secondary text-primary"><x-icon name="shield-check" class="h-5 w-5" /></span>
                    <p class="text-base text-muted-foreground">Superadmins can open and change everything in the console, including users, roles and settings. There is nothing extra to configure for this role — keep the number of superadmins small.</p>
                </div>
            </x-admin.panel>
        </div>

        <div class="flex gap-3">
            <x-button type="submit" variant="primary" icon="save">{{ $editing ? 'Save changes' : 'Create user' }}</x-button>
            <x-button :href="route('admin.users.index')" variant="ghost">Cancel</x-button>
        </div>
    </form>
</x-layouts.admin>
