<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SOW A.20 — audit trail; rows older than 30 days are pruned by the scheduler.
 */
#[Fillable(['user_id', 'module', 'action', 'record_id', 'payload', 'remarks', 'ip_address'])]
class ActivityLog extends Model
{
    use MassPrunable;

    public const RETENTION_DAYS = 30;

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(self::RETENTION_DAYS));
    }
}
