<?php

namespace Database\Seeders;

use App\Enums\RecordStatus;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Creates the first superadmin. Set SUPERADMIN_EMAIL / SUPERADMIN_PASSWORD in
 * .env before seeding production; locally the password defaults to "password".
 */
class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SUPERADMIN_PASSWORD') ?: (app()->environment(['local', 'testing']) ? 'password' : Str::password(16));

        $user = User::firstOrCreate(
            ['email' => env('SUPERADMIN_EMAIL', 'admin@moslegis.com')],
            [
                'name' => 'Super Admin',
                'password' => $password,
                'status' => RecordStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        $user->assignRole(RoleName::Superadmin->value);

        if ($user->wasRecentlyCreated && ! env('SUPERADMIN_PASSWORD') && ! app()->environment(['local', 'testing'])) {
            $this->command?->warn("Superadmin {$user->email} created with generated password: {$password}");
        }
    }
}
