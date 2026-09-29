<?php

namespace App\Http\Requests\Admin;

use App\Models\AuthorCategory;
use App\Models\ContentCategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ManuscriptFeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('fees.edit');
    }

    public function rules(): array
    {
        return [
            'fees' => ['array'],
            'fees.*' => ['array'],
            'fees.*.*' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            $authorIds = AuthorCategory::pluck('id')->all();
            $contentIds = ContentCategory::pluck('id')->all();

            foreach ((array) $this->input('fees', []) as $authorId => $row) {
                foreach ((array) $row as $contentId => $amount) {
                    if (! in_array((int) $authorId, $authorIds, true) || ! in_array((int) $contentId, $contentIds, true)) {
                        $validator->errors()->add('fees', 'The fee matrix references an unknown category.');

                        return;
                    }
                }
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        // Treat empty cells as "not offered" (null) rather than zero.
        $this->merge(['fees' => collect((array) $this->input('fees', []))
            ->map(fn ($row) => collect((array) $row)->map(fn ($v) => $v === '' ? null : $v)->all())
            ->all()]);
    }
}
