<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('blog_category') ? 'blog-categories.edit' : 'blog-categories.create');
    }

    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:140', 'alpha_dash', Rule::unique('blog_categories', 'slug')->ignore($this->route('blog_category'))],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(RecordStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) ($this->input('slug') ?: $this->input('category_name')))]);
    }
}
