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
            'coauthor_fees' => ['array'],
            'coauthor_fees.*' => ['array'],
            'coauthor_fees.*.first_two' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'coauthor_fees.*.additional' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
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

            foreach (array_keys((array) $this->input('coauthor_fees', [])) as $contentId) {
                if (! in_array((int) $contentId, $contentIds, true)) {
                    $validator->errors()->add('coauthor_fees', 'The co-author surcharge references an unknown content category.');

                    return;
                }
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        // Treat empty cells as "not offered" (null) rather than zero.
        $blankToNull = fn ($rows) => collect((array) $rows)
            ->map(fn ($row) => collect((array) $row)->map(fn ($v) => $v === '' ? null : $v)->all())
            ->all();

        $this->merge(['fees' => $blankToNull($this->input('fees', []))]);

        if ($this->has('coauthor_fees')) {
            $this->merge(['coauthor_fees' => $blankToNull($this->input('coauthor_fees'))]);
        }
    }
}
