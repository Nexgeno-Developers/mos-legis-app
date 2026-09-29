<?php

namespace App\Models;

use App\Enums\PlagiarismCheckStatus;
use App\Enums\PlagiarismCheckType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * SOW A.18 — manuscript-submission checks and paid standalone checks share this table.
 */
#[Fillable([
    'user_id', 'manuscript_submission_id', 'check_type', 'title', 'content', 'uploaded_file',
    'similarity_percentage', 'check_status', 'api_response', 'report_file', 'payment_id', 'checked_at',
])]
class PlagiarismCheck extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'check_type' => PlagiarismCheckType::class,
            'check_status' => PlagiarismCheckStatus::class,
            'similarity_percentage' => 'decimal:2',
            'api_response' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ManuscriptSubmission::class, 'manuscript_submission_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /** Standalone checks own their payment through the polymorphic side. */
    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function isCompleted(): bool
    {
        return $this->check_status === PlagiarismCheckStatus::Completed;
    }
}
