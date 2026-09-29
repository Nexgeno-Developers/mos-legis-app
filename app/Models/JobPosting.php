<?php

namespace App\Models;

use App\Enums\ApplicationMethod;
use App\Enums\EmploymentType;
use App\Enums\RecordStatus;
use App\Enums\WorkMode;
use App\Models\Concerns\HasRecordStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SOW A.04 / B.06 / C.03 — live when Active and not past expiry_date.
 */
#[Fillable([
    'user_id', 'job_title', 'organisation', 'location', 'work_mode', 'employment_type', 'experience',
    'practice_area', 'salary', 'summary', 'responsibilities', 'qualifications', 'required_skills',
    'application_method', 'application_email_url', 'application_deadline', 'published_date', 'expiry_date',
    'source_name', 'source_url', 'status',
])]
class JobPosting extends Model
{
    use HasFactory, HasRecordStatus;

    protected function casts(): array
    {
        return [
            'work_mode' => WorkMode::class,
            'employment_type' => EmploymentType::class,
            'application_method' => ApplicationMethod::class,
            'status' => RecordStatus::class,
            'application_deadline' => 'date',
            'published_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date->lt(today());
    }

    #[Scope]
    protected function live(Builder $query): void
    {
        $query->where('status', RecordStatus::Active)
            ->whereDate('expiry_date', '>=', today())
            ->whereDate('published_date', '<=', today());
    }

    #[Scope]
    protected function expired(Builder $query): void
    {
        $query->whereDate('expiry_date', '<', today());
    }
}
