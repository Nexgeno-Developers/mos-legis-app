<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('content_category') ? 'content-categories.edit' : 'content-categories.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('manuscript_content_categories', 'name')->ignore($this->route('content_category'))],
            'min_word_limit' => ['required', 'integer', 'min:0', 'max:1000000'],
            'max_word_limit' => ['required', 'integer', 'gte:min_word_limit', 'max:1000000'],
            'guideline' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
        ];
    }
}
