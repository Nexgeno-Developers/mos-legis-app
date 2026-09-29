<?php

namespace App\Services\Manuscripts;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

/**
 * Reviewer allocation from the SOW clarifications: active reviewers covering the
 * manuscript's content category; fewest active assignments first; ties go to the
 * reviewer who has waited longest since their last assignment (never assigned first).
 */
class ReviewerAllocator
{
    public function pick(int $contentCategoryId): ?User
    {
        return $this->ranked($contentCategoryId)->first();
    }

    /** @return Collection<int, User> */
    public function eligible(int $contentCategoryId): Collection
    {
        return $this->ranked($contentCategoryId)->get();
    }

    private function ranked(int $contentCategoryId): Builder
    {
        return User::query()
            ->active()
            ->role(RoleName::Reviewer->value)
            ->whereHas('reviewerContentCategories', fn (Builder $q) => $q->whereKey($contentCategoryId))
            ->withCount('activeAssignments')
            ->withMax('assignedSubmissions as last_assigned_at', 'assigned_at')
            ->orderBy('active_assignments_count')
            ->orderByRaw('last_assigned_at IS NOT NULL')
            ->orderBy('last_assigned_at')
            ->orderBy('users.id');
    }
}
