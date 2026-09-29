<?php

namespace App\Policies;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.edit');
    }

    public function delete(User $user, User $model): Response
    {
        if ($user->is($model)) {
            return Response::deny('You cannot delete your own account.');
        }

        if ($model->hasRole(RoleName::Superadmin->value) && User::role(RoleName::Superadmin->value)->count() <= 1) {
            return Response::deny('The last superadmin cannot be deleted.');
        }

        if ($model->submissions()->exists() || $model->payments()->exists()) {
            return Response::deny('This user has manuscripts or payments on record — deactivate the account instead.');
        }

        return $user->can('users.delete') ? Response::allow() : Response::deny();
    }
}
