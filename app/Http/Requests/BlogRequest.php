<?php

namespace App\Http\Requests;

use App\Enums\BlogStatus;
use App\Models\Blog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Blog fields from SOW A.05; shared by the admin panel and the author portal (B.05).
 */
class BlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        $blog = $this->route('blog');

        return $blog ? $this->user()->can('update', $blog) : $this->user()->can('create', Blog::class);
    }

    public function rules(): array
    {
        $isAdmin = $this->routeIs('admin.*');

        return [
            'blog_title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:280', 'alpha_dash'],
            'category_id' => ['required', 'integer', Rule::exists('blog_categories', 'id')->where('status', 'Active')],
            'author_name' => [$isAdmin ? 'required' : 'nullable', 'string', 'max:150'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'excerpt' => ['required', 'string', 'max:500'],
            'tag_ids' => ['array', 'max:15'],
            'tag_ids.*' => ['integer', 'exists:blog_tags,id'],
            'content' => ['required', 'string', 'max:500000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'og_image' => ['nullable', 'image', 'max:2048'],
            // Authors choose Draft or Published; Pending is set by the approval rule.
            'status' => ['required', Rule::in($isAdmin ? BlogStatus::values() : [BlogStatus::Draft->value, BlogStatus::Published->value])],
            'publish_date' => ['required', 'date'],
            'featured_post' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return ['blog_title' => 'title', 'category_id' => 'category', 'tag_ids' => 'tags'];
    }
}
