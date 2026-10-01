<?php

namespace App\Actions\Users;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

/**
 * Creates or updates a user together with the data that belongs to their role (SOW A.09):
 * reviewer → content categories; author → author profile and billing address;
 * staff roles → extra permissions granted on top of the role.
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

            $role = $data['role'];
            $user->syncRoles([$role]);

            $user->reviewerContentCategories()->sync($role === RoleName::Reviewer->value ? $data['content_category_ids'] : []);

            if ($role === RoleName::Author->value) {
                $this->saveAuthorData($user, $data);
            }

            // Extra permissions: only what the role does not already grant. Authors and superadmins have none.
            if (array_key_exists('permissions', $data) || in_array($role, [RoleName::Author->value, RoleName::Superadmin->value], true)) {
                $fromRole = Role::findByName($role, 'web')->permissions->pluck('name')->all();
                $user->syncPermissions(array_values(array_diff($data['permissions'] ?? [], $fromRole)));
            }

            return $user;
        });
    }

    private function saveAuthorData(User $user, array $data): void
    {
        $profile = collect($data)->only(['author_category_id', 'institution', 'country', 'bio', 'orcid'])->all();
        $current = $user->authorProfile?->profile_picture;

        if (($data['profile_picture'] ?? null) instanceof UploadedFile) {
            $profile['profile_picture'] = $data['profile_picture']->store('profiles', 'public');
        } elseif (! empty($data['remove_profile_picture'])) {
            $profile['profile_picture'] = null;
        }

        if (array_key_exists('profile_picture', $profile) && $current) {
            Storage::disk('public')->delete($current);
        }

        $user->authorProfile()->updateOrCreate([], $profile);

        if (! empty($data['address'])) {
            $user->address()->updateOrCreate([], $data['address']);
        }
    }
}
