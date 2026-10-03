<?php

namespace App\Http\Requests\Account;

use App\Support\PhoneNumbers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Billing address (clarification #5). The country decides whether tax applies.
 */
class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return self::addressRules();
    }

    /** @return array<string, array<int, mixed>> */
    public static function addressRules(string $prefix = ''): array
    {
        return [
            "{$prefix}recipient_name" => ['required', 'string', 'max:150'],
            "{$prefix}organization_name" => ['nullable', 'string', 'max:190'],
            "{$prefix}phone" => PhoneNumbers::rules(),
            "{$prefix}address_line1" => ['required', 'string', 'max:255'],
            "{$prefix}address_line2" => ['nullable', 'string', 'max:255'],
            "{$prefix}country_code" => ['required', 'string', 'size:2', Rule::in(array_keys(config('countries')))],
            // Indian addresses: the state decides CGST+SGST vs IGST, so it is required and from the list.
            "{$prefix}state" => ['nullable', 'required_if:'.$prefix.'country_code,IN', 'string', 'max:150',
                Rule::when(fn ($input) => data_get($input, $prefix.'country_code') === 'IN', [Rule::in(config('indian_states'))])],
            "{$prefix}city" => ['required', 'string', 'max:150'],
            "{$prefix}postal_code" => ['nullable', 'string', 'max:20'],
            // Optional GST / VAT / tax ID, printed on the invoice.
            "{$prefix}tax_id_number" => ['nullable', 'string', 'max:40'],
        ];
    }

    public function messages(): array
    {
        return ['state.required_if' => 'Select the state — it is needed for GST.', 'state.in' => 'Select a state from the list.'];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge(['phone' => PhoneNumbers::normalize($this->input('phone'))]);
        }
    }
}
