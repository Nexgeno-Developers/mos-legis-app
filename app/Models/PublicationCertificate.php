<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clarification #1 — generated when a manuscript reaches the published stage.
 */
#[Fillable(['manuscript_submission_id', 'certificate_number', 'document_path', 'verification_slug', 'snapshot_json', 'issued_at'])]
class PublicationCertificate extends Model
{
    protected function casts(): array
    {
        return [
            'snapshot_json' => 'array',
            'issued_at' => 'datetime',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ManuscriptSubmission::class, 'manuscript_submission_id');
    }

    public function verificationUrl(): string
    {
        return route('certificates.verify', $this->verification_slug);
    }
}
