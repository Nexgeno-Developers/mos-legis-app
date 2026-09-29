<?php

namespace App\Policies;

use App\Models\JobPosting;
use App\Models\User;

/**
 * Admin permissions for the console; authors manage only their own listings (SOW B.06).
 */
class JobPostingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('job-postings.view');
    }

    public function view(User $user, JobPosting $job): bool
    {
        return $user->can('job-postings.view') || $job->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->can('job-postings.create') || $user->isAuthor();
    }

    public function update(User $user, JobPosting $job): bool
    {
        return $user->can('job-postings.edit') || ($user->isAuthor() && $job->user_id === $user->id);
    }

    public function delete(User $user, JobPosting $job): bool
    {
        return $user->can('job-postings.delete') || ($user->isAuthor() && $job->user_id === $user->id);
    }
}
