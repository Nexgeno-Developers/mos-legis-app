<?php

namespace App\Models;

use App\Enums\AwardPeriodType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One Best Paper winner per quarter (enforced by the period_key unique index).
 */
#[Fillable([
    'manuscript_submission_id', 'period_type', 'award_month', 'award_quarter', 'award_year',
    'prize_amount', 'editorial_citation', 'selected_at', 'selected_by',
])]
class BestPaperAward extends Model
{
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

    /** The cash prize is optional. */
    public function hasPrize(): bool
    {
        return $this->prize_amount !== null && (float) $this->prize_amount > 0;
    }

    /** Last completed quarter as ['Q1'..'Q4', year]. */
    public static function lastQuarter(): array
    {
        $date = now()->subQuarterNoOverflow();

        return ['Q'.$date->quarter, $date->year];
    }

    /** Sort key: later quarters first when sorted descending. */
    public function periodOrder(): int
    {
        return $this->award_year * 10 + (int) substr((string) $this->award_quarter, 1);
    }

    public function periodLabel(): string
    {
        return $this->award_quarter.' '.$this->award_year;
    }
}
