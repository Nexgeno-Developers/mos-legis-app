<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use App\Enums\RoleName;
use App\Http\Requests\Account\AddressRequest;
use App\Models\User;
use App\Support\Permissions;
use App\Support\PhoneNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * SOW A.09 — account fields for every user plus the data that belongs to the chosen role:
 * - author: author profile (category, institution, country, ORCID iD, bio, photo) and billing address;
 * - reviewer: content categories (multi);
 * - reviewer / custom staff roles: extra permissions on top of the role (superadmin only).
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user ? $this->user()->can('update', $user) : $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $author = RoleName::Author->value;

        $rules = [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => PhoneNumbers::rules(),
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],

            // Reviewer
            'content_category_ids' => ['exclude_unless:role,'.RoleName::Reviewer->value, 'required', 'array', 'min:1'],
            'content_category_ids.*' => ['integer', 'exists:manuscript_content_categories,id'],

            // Author profile
            'author_category_id' => ["exclude_unless:role,{$author}", 'required', 'integer', 'exists:manuscript_author_categories,id'],
            'institution' => ["exclude_unless:role,{$author}", 'nullable', 'string', 'max:190'],
            'country' => ["exclude_unless:role,{$author}", 'nullable', 'string', 'max:100'],
            'bio' => ["exclude_unless:role,{$author}", 'nullable', 'string', 'max:3000'],
            'orcid' => ["exclude_unless:role,{$author}", 'nullable', 'string', 'regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/',
                Rule::unique('author_profiles', 'orcid')->ignore($user?->id, 'user_id')],
            'profile_picture' => ["exclude_unless:role,{$author}", 'nullable', 'image', 'max:2048'],
            'remove_profile_picture' => ["exclude_unless:role,{$author}", 'boolean'],
        ];

        // Billing address: optional, but complete once any part of it is filled in.
        if ($this->input('role') === $author && $this->hasAddress()) {
            $rules += AddressRequest::addressRules('address.');
        }

        // Extra permissions for staff roles; only a superadmin may grant them.
        if ($this->user()->isSuperadmin() && ! in_array($this->input('role'), [$author, RoleName::Superadmin->value], true)) {
            $rules['permissions'] = ['nullable', 'array'];
            $rules['permissions.*'] = ['string', Rule::in(Permissions::all())];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'orcid.regex' => 'Enter the ORCID iD as 0000-0000-0000-0000.',
            'orcid.unique' => 'This ORCID iD is already linked to another author.',
        ];
    }

    public function attributes(): array
    {
        return [
            'content_category_ids' => 'content categories',
            'author_category_id' => 'author category',
            'address.recipient_name' => 'recipient name',
            'address.address_line1' => 'address line 1',
            'address.country_code' => 'country',
            'address.city' => 'city',
            'address.tax_id_type' => 'tax ID type',
            'address.tax_id_number' => 'tax ID number',
            'address.phone' => 'billing phone',
        ];
    }

    private function hasAddress(): bool
    {
        return collect($this->input('address', []))
            ->except(['country_code', 'tax_id_type'])
            ->filter(fn ($value) => filled($value))
            ->isNotEmpty();
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge(['phone' => PhoneNumbers::normalize($this->input('phone'))]);
        }

        if ($this->has('address.phone')) {
            $this->merge(['address' => ['phone' => PhoneNumbers::normalize($this->input('address.phone'))] + $this->input('address', [])]);
        }

        if ($this->filled('orcid')) {
            $this->merge(['orcid' => strtoupper(preg_replace('#^https?://orcid\.org/#i', '', trim($this->input('orcid'))))]);
        }

        // Only a superadmin may grant the superadmin role.
        if ($this->input('role') === RoleName::Superadmin->value && ! $this->user()->isSuperadmin()) {
            $this->merge(['role' => null]);
        }
    }
}
