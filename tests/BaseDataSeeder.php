<?php

namespace Tests;

use Database\Seeders\MenuSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Database\Seeder;

/**
 * Roles, permissions, settings and the default website menus every test needs.
 */
class BaseDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([RolePermissionSeeder::class, SettingSeeder::class, MenuSeeder::class]);
    }
}
