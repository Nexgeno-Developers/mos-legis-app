<?php

namespace App\Models\Concerns;

use App\Enums\RecordStatus;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared behaviour for tables with the Active/Inactive status column.
 */
trait HasRecordStatus
{
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where($this->qualifyColumn('status'), RecordStatus::Active);
    }

    public function isActive(): bool
    {
        return $this->status === RecordStatus::Active;
    }
}
