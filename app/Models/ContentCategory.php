<?php

namespace App\Models;

use App\Enums\RecordStatus;
use App\Models\Concerns\HasRecordStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * SOW A.13 — the other axis of the fee matrix; drives word-count validation.
 */
#[Table('manuscript_content_categories')]
#[Fillable(['name', 'min_word_limit', 'max_word_limit', 'guideline', 'status'])]
class ContentCategory extends Model
{
    use HasFactory, HasRecordStatus;

    protected function casts(): array
    {
        return [
            'status' => RecordStatus::class,
            'min_word_limit' => 'integer',
            'max_word_limit' => 'integer',
        ];
    }

    public function themes(): HasMany
    {
        return $this->hasMany(ContentCategoryTheme::class, 'content_category_id');
    }

    /** This month's theme, if the admin has set one (SOW A.14). */
    public function currentTheme(): HasOne
    {
        return $this->hasOne(ContentCategoryTheme::class, 'content_category_id')
            ->ofMany(['id' => 'max'], fn ($q) => $q->whereDate('period', now()->startOfMonth()));
    }

    public function fees(): HasMany
    {
        return $this->hasMany(ManuscriptFee::class, 'content_category_id');
    }

    public function reviewers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reviewer_content_categories', 'content_category_id', 'user_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ManuscriptSubmission::class, 'content_category_id');
    }

    public function wordLimitLabel(): string
    {
        return number_format($this->min_word_limit).'–'.number_format($this->max_word_limit).' words';
    }
}
