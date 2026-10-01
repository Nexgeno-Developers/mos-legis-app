<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use App\Enums\RoleName;
use App\Models\User;
use App\Support\PhoneNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * SOW A.09 — reviewer role requires content categories (multi); author role requires one author category.
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

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => PhoneNumbers::rules(),
            'status' => ['required', Rule::enum(RecordStatus::class)],
            'role' => ['required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'content_category_ids' => ['exclude_unless:role,'.RoleName::Reviewer->value, 'required', 'array', 'min:1'],
            'content_category_ids.*' => ['integer', 'exists:manuscript_content_categories,id'],
            'author_category_id' => ['exclude_unless:role,'.RoleName::Author->value, 'required', 'integer', 'exists:manuscript_author_categories,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'content_category_ids' => 'content categories',
            'author_category_id' => 'author category',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge(['phone' => PhoneNumbers::normalize($this->input('phone'))]);
        }

        // Only a superadmin may grant the superadmin role.
        if ($this->input('role') === RoleName::Superadmin->value && ! $this->user()->isSuperadmin()) {
            $this->merge(['role' => null]);
        }
    }
}
