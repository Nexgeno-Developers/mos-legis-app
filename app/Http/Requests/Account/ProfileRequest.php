<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * SOW B.02 + clarification #4: account fields and the author profile.
 */
class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAuthor();
    }

    public function rules(): array
    {
        $hasPassword = $this->user()->password !== null;

        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'author_category_id' => ['required', 'integer', Rule::exists('manuscript_author_categories', 'id')->where('status', 'Active')],
            'institution' => ['nullable', 'string', 'max:190'],
            'country' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'orcid' => ['nullable', 'string', 'regex:/^\d{4}-\d{4}-\d{4}-\d{3}[\dX]$/'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'current_password' => [$hasPassword ? 'nullable' : 'prohibited', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return ['orcid.regex' => 'Enter the ORCID iD as 0000-0000-0000-0000.'];
    }
}
