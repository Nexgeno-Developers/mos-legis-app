<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SOW A.14 — one record holds volume, theme name and period,
 * e.g. "Vol. 5 · Artificial Intelligence in Law · September 2026".
 */
#[Table('manuscript_content_category_themes')]
#[Fillable(['content_category_id', 'name', 'volume', 'period'])]
class ContentCategoryTheme extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['period' => 'date', 'volume' => 'integer'];
    }

    /**
     * The theme authors write to this month (SOW A.14). Categories without a
     * theme for the current month simply have none.
     */
    public static function currentFor(int $contentCategoryId): ?self
    {
        return static::query()
            ->where('content_category_id', $contentCategoryId)
            ->whereDate('period', now()->startOfMonth())
            ->latest('id')
            ->first();
    }

    public function contentCategory(): BelongsTo
    {
        return $this->belongsTo(ContentCategory::class, 'content_category_id');
    }

    public function periodLabel(): string
    {
        return $this->period->format('F Y');
    }

    public function fullLabel(): string
    {
        return "Vol. {$this->volume} · {$this->name} · {$this->periodLabel()}";
    }
}
