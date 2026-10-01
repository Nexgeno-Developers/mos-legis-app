<?php

namespace App\Actions\Manuscripts;

use App\Http\Requests\ManuscriptSubmissionRequest;
use App\Models\ContentCategoryTheme;
use App\Models\ManuscriptSubmission;
use App\Models\User;
use App\Services\Manuscripts\ManuscriptWorkflow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Stores the manuscript file privately and creates/updates the submission.
 * The current month's theme of the content category (if any) is attached (SOW A.14).
 */
class SaveSubmission
{
    public function __construct(private readonly ManuscriptWorkflow $workflow) {}

    public function create(ManuscriptSubmissionRequest $request, User $author): ManuscriptSubmission
    {
        $path = $request->file('manuscript')->store("manuscripts/{$author->id}", 'local');

        try {
            return DB::transaction(function () use ($request, $author, $path) {
                $submission = new ManuscriptSubmission($this->attributes($request) + [
                    'user_id' => $author->id,
                    'manuscript_attachment' => $path,
                    'word_count' => $request->countedWords(),
                ]);
                $submission->content_category_theme_id = ContentCategoryTheme::currentFor($submission->content_category_id)?->id;
                $submission->save();

                $this->workflow->submitted($submission);

                return $submission;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);

            throw $e;
        }
    }

    public function update(ManuscriptSubmissionRequest $request, ManuscriptSubmission $submission): ManuscriptSubmission
    {
        $attributes = $this->attributes($request);

        if ($file = $request->file('manuscript')) {
            $attributes['manuscript_attachment'] = $file->store("manuscripts/{$submission->user_id}", 'local');
            $attributes['word_count'] = $request->countedWords();
        }

        $submission->update($attributes);

        return $submission;
    }

    private function attributes(ManuscriptSubmissionRequest $request): array
    {
        return $request->safe()->only([
            'author_category_id', 'institution', 'country', 'title', 'content_category_id', 'abstract',
            ...array_keys(ManuscriptSubmission::DECLARATIONS),
        ]) + [
            'keywords' => $request->keywords(),
            'co_authors' => $request->coAuthors(),
        ];
    }
}
