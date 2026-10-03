<?php

namespace App\Http\Requests\Admin;

use App\Enums\PageTemplate;
use App\Enums\PublishStatus;
use App\Support\GoogleMap;
use App\Support\PageTemplates;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can($this->route('page') ? 'pages.edit' : 'pages.create');
    }

    public function rules(): array
    {
        $page = $this->route('page');
        // The template is fixed once a page exists (it decides which metas are stored).
        $template = $page?->template ?? PageTemplate::tryFrom((string) $this->input('template'));

        $rules = [
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:220', 'alpha_dash', Rule::unique('pages', 'slug')->ignore($page)],
            'template' => [$page ? 'prohibited' : 'required', Rule::enum(PageTemplate::class)],
            'status' => ['required', Rule::enum(PublishStatus::class)],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:500000'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
            'og_image' => ['nullable', 'image', 'max:2048'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'meta' => ['array'],
        ];

        foreach ($template ? PageTemplates::fields($template) : [] as $key => $field) {
            if ($field['type'] === 'sections') {
                $rules["meta.{$key}"] = ['nullable', 'array'];
                foreach (array_keys(PageTemplates::TEAM_SECTIONS) as $section) {
                    $rules["meta.{$key}.{$section}.label"] = ['nullable', 'string', 'max:60'];
                    $rules["meta.{$key}.{$section}.heading"] = ['nullable', 'string', 'max:120'];
                }
            } elseif ($field['type'] === 'repeater') {
                $rules["meta.{$key}"] = ['array', 'max:200'];
                foreach ($field['columns'] as $column => $definition) {
                    $rules["meta.{$key}.*.{$column}"] = $definition['type'] === 'select'
                        ? ['nullable', Rule::in(array_keys($definition['options']))]
                        : ['nullable', 'string', $definition['type'] === 'textarea' ? 'max:5000' : 'max:255'];
                }
            } else {
                $rules["meta.{$key}"] = match ($field['type']) {
                    'email' => ['nullable', 'string', 'email'],
                    // Accepts a Google Maps embed URL or the full <iframe> snippet; only Google Maps is ever embedded.
                    'map' => ['nullable', 'string', 'max:3000', function (string $attribute, mixed $value, Closure $fail) {
                        if (filled($value) && ! GoogleMap::embedUrl($value)) {
                            $fail('Paste a Google Maps embed link (Share → Embed a map) or leave this blank.');
                        }
                    }],
                    default => ['nullable', 'string', 'max:5000'],
                };
            }
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug((string) ($this->input('slug') ?: $this->input('title'))),
        ]);
    }
}
