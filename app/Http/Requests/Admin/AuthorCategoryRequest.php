<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AuthorCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('author_category') ? 'author-categories.edit' : 'author-categories.create');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120', Rule::unique('manuscript_author_categories', 'name')->ignore($this->route('author_category'))],
            'status' => ['required', Rule::enum(RecordStatus::class)],
        ];
    }
}
