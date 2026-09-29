<?php

namespace App\Http\Requests\Admin;

use App\Enums\CommentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('blog_comment') ? 'blog-comments.edit' : 'blog-comments.create');
    }

    public function rules(): array
    {
        return [
            'blog_id' => [$this->route('blog_comment') ? 'prohibited' : 'required', 'integer', 'exists:blogs,id'],
            'comment' => ['required', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(CommentStatus::class)],
        ];
    }
}
