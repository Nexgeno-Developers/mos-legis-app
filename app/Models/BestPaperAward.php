<?php

namespace App\Models;

use App\Enums\AwardPeriodType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Clarification #3 — one winner per monthly or quarterly period (enforced by the period_key unique index).
 */
#[Fillable([
    'manuscript_submission_id', 'period_type', 'award_month', 'award_quarter', 'award_year',
    'prize_amount', 'editorial_citation', 'selected_at', 'selected_by',
])]
class BestPaperAward extends Model
{
    public const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    public const QUARTERS = ['Q1', 'Q2', 'Q3', 'Q4'];

    protected function casts(): array
    {
        return [
            'period_type' => AwardPeriodType::class,
            'award_year' => 'integer',
            'prize_amount' => 'decimal:2',
            'selected_at' => 'date',
        ];
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ManuscriptSubmission::class, 'manuscript_submission_id');
    }

    public function selector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by');
    }

    public function periodLabel(): string
    {
        return ($this->award_month ?? $this->award_quarter).' '.$this->award_year;
    }
}
