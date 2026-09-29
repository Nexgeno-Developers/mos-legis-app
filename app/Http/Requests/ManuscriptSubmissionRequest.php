<?php

namespace App\Http\Requests;

use App\Models\AuthorProfile;
use App\Models\ContentCategory;
use App\Models\ManuscriptSubmission;
use App\Services\Manuscripts\DocxWordCounter;
use App\Services\Manuscripts\FeeCalculator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Throwable;

/**
 * SOW A.16 submission fields (Author details, Manuscript information, Confirmations).
 * Used by the author step form (B.04) and by admins creating/editing on an author's behalf.
 * The word count is recounted from the uploaded .docx and must fit the category limits.
 */
class ManuscriptSubmissionRequest extends FormRequest
{
    private ?int $countedWords = null;

    public function authorize(): bool
    {
        $submission = $this->route('submission');

        if ($submission) {
            return $this->user()->can('update', $submission);
        }

        return $this->isAdmin() ? $this->user()->can('create', ManuscriptSubmission::class) : $this->user()->isAuthor();
    }

    public function rules(): array
    {
        $editing = $this->route('submission') !== null;

        $rules = [
            'author_category_id' => ['required', 'integer', Rule::exists('manuscript_author_categories', 'id')->where('status', 'Active')],
            'institution' => ['required', 'string', 'max:190'],
            'country' => ['required', 'string', 'max:100'],
            'co_authors' => ['array', 'max:10'],
            'co_authors.*' => ['nullable', 'string', 'max:150'],
            'title' => ['required', 'string', 'max:255'],
            'content_category_id' => ['required', 'integer', Rule::exists('manuscript_content_categories', 'id')->where('status', 'Active')],
            'keywords' => ['required', 'string', 'max:500'],
            'abstract' => ['required', 'string', 'max:5000'],
            'manuscript' => [$editing ? 'nullable' : 'required', 'file', 'mimes:docx', 'mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip,application/octet-stream', 'max:20480'],
        ];

        foreach (array_keys(ManuscriptSubmission::DECLARATIONS) as $field) {
            $rules[$field] = $editing ? ['boolean'] : ['accepted'];
        }

        if ($this->isAdmin() && ! $editing) {
            $rules['user_id'] = ['required', 'integer', Rule::exists('users', 'id')];
        }

        return $rules;
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $keywords = $this->keywords();
            if (count($keywords) < 3 || count($keywords) > 6) {
                $validator->errors()->add('keywords', 'Enter between 3 and 6 keywords, separated by commas.');
            }

            if (app(FeeCalculator::class)->publicationFee((int) $this->input('author_category_id'), (int) $this->input('content_category_id')) === null) {
                $validator->errors()->add('content_category_id', 'This content category is not currently offered for your author category.');
            }

            if ($file = $this->file('manuscript')) {
                try {
                    $this->countedWords = app(DocxWordCounter::class)->count($file->getRealPath());
                } catch (Throwable) {
                    $validator->errors()->add('manuscript', 'The file could not be read. Upload a valid .docx document.');

                    return;
                }

                $category = ContentCategory::find($this->input('content_category_id'));
                if ($category && ($this->countedWords < $category->min_word_limit || $this->countedWords > $category->max_word_limit)) {
                    $validator->errors()->add('manuscript', "The manuscript has {$this->countedWords} words; {$category->name} accepts {$category->wordLimitLabel()}.");
                }
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'author_category_id' => 'author category',
            'content_category_id' => 'content category',
            'user_id' => 'author',
        ] + array_map(fn () => 'declaration', ManuscriptSubmission::DECLARATIONS);
    }

    public function messages(): array
    {
        return array_combine(
            array_map(fn ($f) => "{$f}.accepted", array_keys(ManuscriptSubmission::DECLARATIONS)),
            array_fill(0, count(ManuscriptSubmission::DECLARATIONS), 'Please accept this declaration.'),
        );
    }

    /** @return list<string> */
    public function keywords(): array
    {
        return collect(explode(',', (string) $this->input('keywords')))
            ->map(fn ($k) => trim($k))->filter()->unique()->values()->all();
    }

    /** @return list<string> */
    public function coAuthors(): array
    {
        return collect($this->input('co_authors', []))->map(fn ($n) => trim((string) $n))->filter()->values()->all();
    }

    public function countedWords(): ?int
    {
        return $this->countedWords;
    }

    private function isAdmin(): bool
    {
        return $this->routeIs('admin.*');
    }

    protected function prepareForValidation(): void
    {
        // Pre-fill author category/institution from the author's profile when omitted.
        if (! $this->isAdmin() && $profile = AuthorProfile::firstWhere('user_id', $this->user()?->id)) {
            $this->mergeIfMissing(array_filter([
                'author_category_id' => $profile->author_category_id,
                'institution' => $profile->institution,
                'country' => $profile->country,
            ]));
        }
    }
}
