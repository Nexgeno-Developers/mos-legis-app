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
 * Author category, institution and country are not entered on the form: a new submission takes
 * them from the author's profile (a snapshot, so later profile edits do not change it), and an
 * edited submission keeps the values it was submitted with.
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
            'author_category_id' => ['required', 'integer', $editing ? Rule::exists('manuscript_author_categories', 'id') : Rule::exists('manuscript_author_categories', 'id')->where('status', 'Active')],
            'institution' => ['required', 'string', 'max:190'],
            'country' => ['nullable', 'string', 'max:100'],
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
        $whose = $this->isAdmin() ? 'The author’s profile' : 'Your profile';

        return array_combine(
            array_map(fn ($f) => "{$f}.accepted", array_keys(ManuscriptSubmission::DECLARATIONS)),
            array_fill(0, count(ManuscriptSubmission::DECLARATIONS), 'Please accept this declaration.'),
        ) + [
            'author_category_id.required' => "{$whose} has no author category yet. Add it to the profile first.",
            'author_category_id.exists' => "{$whose} has an author category that is no longer offered. Update the profile first.",
            'institution.required' => "{$whose} has no institution yet. Add it to the profile first.",
        ];
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
        $this->merge(self::authorDetails($this->route('submission'), $this->isAdmin() ? $this->integer('user_id') : $this->user()?->id));
    }

    /**
     * Author category, institution and country for a submission: the recorded values when editing,
     * otherwise the author's profile (country falls back to the billing address country).
     *
     * @return array{author_category_id: ?int, institution: ?string, country: ?string}
     */
    public static function authorDetails(?ManuscriptSubmission $submission, ?int $authorId): array
    {
        if ($submission) {
            return $submission->only(['author_category_id', 'institution', 'country']);
        }

        $profile = $authorId ? AuthorProfile::with('user.address')->firstWhere('user_id', $authorId) : null;
        $billingCountry = $profile?->user?->address ? (config('countries')[$profile->user->address->country_code] ?? null) : null;

        return [
            'author_category_id' => $profile?->author_category_id,
            'institution' => $profile?->institution,
            'country' => $profile?->country ?: $billingCountry,
        ];
    }
}
