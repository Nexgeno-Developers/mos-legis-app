<?php

namespace Tests;

use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Database\Seeder;

/**
 * Roles, permissions and settings every test needs.
 */
class BaseDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolePermissionSeeder::class, SettingSeeder::class]);
    }
}
