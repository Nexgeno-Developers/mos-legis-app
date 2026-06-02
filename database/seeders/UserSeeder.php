<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the company
        $company = Company::where('email', 'info@example.com')->first();

        if (!$company) {
            return;
        }

        // Seed a single superadmin user linked to the company (1 record in users table)
        $superAdminRole = Role::where('name', 'superadmin')->first();
        $superAdminUser = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Administrator',
                'role_id' => $superAdminRole?->id,
                'company_id' => $company->id,
                'password' => bcrypt('superadmin@example.com'), // change in production
            ],
        );

        // Ensure the user also has the Spatie role so permission middleware passes
        if ($superAdminUser && $superAdminRole) {
            $superAdminUser->syncRoles([$superAdminRole->name]);
        }
    }
}
