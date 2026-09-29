<?php

namespace App\Http\Requests\Account;

use App\Enums\TaxIdType;
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
            "{$prefix}phone" => ['nullable', 'string', 'max:30'],
            "{$prefix}address_line1" => ['required', 'string', 'max:255'],
            "{$prefix}address_line2" => ['nullable', 'string', 'max:255'],
            "{$prefix}country_code" => ['required', 'string', 'size:2', Rule::in(array_keys(config('countries')))],
            "{$prefix}state" => ['nullable', 'string', 'max:150'],
            "{$prefix}city" => ['required', 'string', 'max:150'],
            "{$prefix}postal_code" => ['nullable', 'string', 'max:20'],
            "{$prefix}tax_id_type" => ['required', Rule::enum(TaxIdType::class)],
            "{$prefix}tax_id_number" => ['nullable', 'required_unless:'.$prefix.'tax_id_type,none', 'string', 'max:40'],
        ];
    }
}
