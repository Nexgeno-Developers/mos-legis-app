<?php

namespace App\Http\Requests\Admin;

use App\Enums\RecordStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('blog_tag') ? 'blog-tags.edit' : 'blog-tags.create');
    }

    public function rules(): array
    {
        return [
            'tag_name' => ['required', 'string', 'max:80'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('blog_tags', 'slug')->ignore($this->route('blog_tag'))],
            'status' => ['required', Rule::enum(RecordStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug((string) ($this->input('slug') ?: $this->input('tag_name')))]);
    }
}
