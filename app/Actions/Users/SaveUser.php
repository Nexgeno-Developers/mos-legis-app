<?php

namespace App\Actions\Users;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Creates or updates a user together with the role-specific data from SOW A.09:
 * reviewer → content categories, author → author profile category.
 */
class SaveUser
{
    /** @param array<string, mixed> $data validated UserRequest data */
    public function handle(array $data, ?User $user = null): User
    {
        return DB::transaction(function () use ($data, $user) {
            $attributes = collect($data)->only(['name', 'email', 'phone', 'status'])->all();

            if (! empty($data['password'])) {
                $attributes['password'] = $data['password'];
            }

            if ($user) {
                $user->update($attributes);
            } else {
                $user = User::create($attributes + ['email_verified_at' => now()]);
            }

            $user->syncRoles([$data['role']]);

            $user->reviewerContentCategories()->sync(
                $data['role'] === RoleName::Reviewer->value ? $data['content_category_ids'] : []
            );

            if ($data['role'] === RoleName::Author->value) {
                $user->authorProfile()->updateOrCreate([], ['author_category_id' => $data['author_category_id']]);
            }

            return $user;
        });
    }
}
