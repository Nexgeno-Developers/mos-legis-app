<?php

namespace App\Models;

use App\Enums\RevisionDecision;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One reviewer decision on a submission, plus the author's resubmission when a revision was requested.
 */
#[Fillable([
    'manuscript_submission_id', 'round', 'reviewer_id', 'decision', 'reviewer_remarks', 'reviewed_attachment',
    'decided_at', 'resubmitted_attachment', 'resubmitted_word_count', 'author_response', 'resubmitted_at',
])]
class ManuscriptRevision extends Model
{
    protected function casts(): array
    {
        return [
            'decision' => RevisionDecision::class,
            'decided_at' => 'datetime',
            'resubmitted_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ManuscriptSubmission::class, 'manuscript_submission_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
