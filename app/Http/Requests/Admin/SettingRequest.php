<?php

namespace App\Http\Requests\Admin;

use App\Support\SettingsRegistry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Inputs are named "<group>_<key>", e.g. manuscript_plagiarism_prescreening_fee.
 */
class SettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('settings.edit');
    }

    public function rules(): array
    {
        $rules = [];

        foreach (SettingsRegistry::GROUPS as $group => $definition) {
            foreach ($definition['fields'] as $key => $field) {
                $rules["{$group}_{$key}"] = match ($field['type']) {
                    'email' => ['nullable', 'email', 'max:190'],
                    'url' => ['nullable', 'url', 'max:255'],
                    'number' => ['required', 'numeric', 'min:0', 'max:99999999'],
                    'boolean' => ['nullable', 'boolean'],
                    'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,ico,svg', 'max:2048'],
                    'select' => ['required', Rule::in($field['options'])],
                    'timezone' => ['required', 'timezone:all'],
                    'textarea' => ['nullable', 'string', 'max:2000'],
                    default => ['nullable', 'string', 'max:255'],
                };
            }
        }

        $rules['general_application_name'] = ['required', 'string', 'max:120'];
        $rules['manuscript_plagiarism_max_similarity_percent'] = ['required', 'numeric', 'min:0', 'max:100'];
        $rules['payment_tax_rate_percent'] = ['required', 'numeric', 'min:0', 'max:100'];

        return $rules;
    }
}
