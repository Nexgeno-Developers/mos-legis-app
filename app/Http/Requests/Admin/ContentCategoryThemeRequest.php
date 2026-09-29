<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class ContentCategoryThemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('theme') ? 'themes.edit' : 'themes.create');
    }

    public function rules(): array
    {
        return [
            'content_category_id' => ['required', 'integer', 'exists:manuscript_content_categories,id'],
            'name' => ['required', 'string', 'max:190'],
            'volume' => ['required', 'integer', 'min:1', 'max:9999'],
            'period' => ['required', 'date_format:Y-m'],
        ];
    }

    /** The <input type="month"> value (YYYY-MM) is stored as the first day of that month. */
    public function payload(): array
    {
        return ['period' => Carbon::createFromFormat('!Y-m', $this->validated('period'))->toDateString()] + $this->validated();
    }
}
