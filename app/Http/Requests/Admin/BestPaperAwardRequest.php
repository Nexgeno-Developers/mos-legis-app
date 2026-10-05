<?php

namespace App\Http\Requests\Admin;

use App\Enums\ManuscriptStage;
use App\Models\BestPaperAward;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Admin → Best Paper Awards: a published manuscript, the quarter it wins, an optional cash prize
 * and the editorial citation shown on the website.
 */
class BestPaperAwardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('submissions.best-paper');
    }

    public function rules(): array
    {
        return [
            'manuscript_submission_id' => ['required', 'integer', Rule::exists('manuscript_submissions', 'id')->where('stage', ManuscriptStage::Published->value)],
            'award_quarter' => ['required', Rule::in(BestPaperAward::QUARTERS)],
            'award_year' => ['required', 'integer', 'between:2000,2100'],
            // Optional: an award can be given without a cash prize.
            'prize_amount' => ['nullable', 'numeric', 'min:0', 'max:10000000'],
            'editorial_citation' => ['required', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'manuscript_submission_id.required' => 'Choose the winning manuscript.',
            'manuscript_submission_id.exists' => 'Only published manuscripts can win Best Paper.',
            'award_quarter.required' => 'Choose the quarter.',
        ];
    }

    public function attributes(): array
    {
        return ['manuscript_submission_id' => 'manuscript', 'award_quarter' => 'quarter', 'award_year' => 'year', 'editorial_citation' => 'citation'];
    }
}
