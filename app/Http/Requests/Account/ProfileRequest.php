<?php

namespace App\Http\Requests\Account;

use App\Support\PhoneNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * SOW B.02 + clarification #4: account fields and the author profile. The ORCID iD is not
 * editable here: it is added once through "Connect your ORCID iD" and then locked.
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
            'phone' => PhoneNumbers::rules(),
            'author_category_id' => ['required', 'integer', Rule::exists('manuscript_author_categories', 'id')->where('status', 'Active')],
            'institution' => ['required', 'string', 'max:190'],
            'country' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:3000'],
            'profile_picture' => ['nullable', 'image', 'max:2048'],
            'current_password' => [$hasPassword ? 'nullable' : 'prohibited', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return ['institution.required' => 'Enter your institution or organisation.'];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge(['phone' => PhoneNumbers::normalize($this->input('phone'))]);
        }
    }
}
