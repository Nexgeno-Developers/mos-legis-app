<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Baseline data every environment needs. Demo content is seeded only in
     * local/testing, or explicitly with `php artisan db:seed --class=DemoSeeder`.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            SettingSeeder::class,
            SuperadminSeeder::class,
            CatalogueSeeder::class,
            PageSeeder::class,
            MenuSeeder::class,
            NotificationTemplateSeeder::class,
        ]);

        if (app()->environment(['local', 'testing'])) {
            $this->call(DemoSeeder::class);
        }
    }
}
