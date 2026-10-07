<?php

namespace App\Http\Requests\Admin;

use App\Models\ContentCategoryTheme;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Validator;

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

    /** A category has one theme per month: it is "this month's theme" on submissions. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->hasAny(['content_category_id', 'period'])) {
                return;
            }

            $taken = ContentCategoryTheme::where('content_category_id', $this->integer('content_category_id'))
                ->whereDate('period', Carbon::createFromFormat('!Y-m', (string) $this->input('period'))->toDateString())
                ->when($this->route('theme'), fn ($q, $theme) => $q->whereKeyNot(is_object($theme) ? $theme->getKey() : $theme))
                ->exists();

            if ($taken) {
                $validator->errors()->add('period', 'This content category already has a theme for that month. Edit the existing theme instead.');
            }
        }];
    }

    /** The <input type="month"> value (YYYY-MM) is stored as the first day of that month. */
    public function payload(): array
    {
        return ['period' => Carbon::createFromFormat('!Y-m', $this->validated('period'))->toDateString()] + $this->validated();
    }
}
