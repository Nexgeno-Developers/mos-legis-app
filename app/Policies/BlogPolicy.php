<?php

namespace App\Policies;

use App\Models\Blog;
use App\Models\User;

/**
 * Admin permissions for the console; authors manage only their own posts (SOW B.05).
 */
class BlogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('blogs.view');
    }

    public function create(User $user): bool
    {
        return $user->can('blogs.create') || $user->isAuthor();
    }

    public function update(User $user, Blog $blog): bool
    {
        return $user->can('blogs.edit') || ($user->isAuthor() && $blog->user_id === $user->id);
    }

    public function delete(User $user, Blog $blog): bool
    {
        return $user->can('blogs.delete') || ($user->isAuthor() && $blog->user_id === $user->id);
    }
}
