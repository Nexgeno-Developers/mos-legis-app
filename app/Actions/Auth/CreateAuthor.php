<?php

namespace App\Actions\Auth;

use App\Enums\RecordStatus;
use App\Enums\RoleName;
use App\Enums\SocialProvider;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Creates a verified author ("Member") account with its author profile and, optionally, a linked social identity.
 */
class CreateAuthor
{
    /**
     * @param  array{name: string, email: string, phone?: ?string, password?: ?string, author_category_id?: ?int, orcid?: ?string}  $data
     */
    public function handle(array $data, ?SocialProvider $provider = null, ?string $providerUserId = null): User
    {
        return DB::transaction(function () use ($data, $provider, $providerUserId) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                // Already hashed when coming from the pending registration in the session.
                'password' => $data['password'] ?? null,
                'status' => RecordStatus::Active,
                'email_verified_at' => now(),
            ]);

            $user->assignRole(RoleName::Author->value);
            $user->authorProfile()->create([
                'author_category_id' => $data['author_category_id'] ?? null,
                'orcid' => $data['orcid'] ?? null,
            ]);

            if ($provider && $providerUserId) {
                $user->socialAccounts()->create(['provider' => $provider, 'provider_user_id' => $providerUserId]);
            }

            activity()->log('Authentication', 'Author registered', $user, ['via' => $provider?->value ?? 'email']);

            return $user;
        });
    }
}
