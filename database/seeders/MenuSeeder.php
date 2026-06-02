<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuGroup;

class MenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed menu groups
        $menuGroups = [
            ['name' => 'Top Navigation', 'slug' => 'top-navigation', 'description' => 'Main top navigation menu', 'status' => true],
            ['name' => 'Quick Link', 'slug' => 'quick-link', 'description' => 'Quick links menu', 'status' => true],
            ['name' => 'Legal', 'slug' => 'legal', 'description' => 'Legal menu', 'status' => true],
            ['name' => 'Connect', 'slug' => 'connect', 'description' => 'Connect menu', 'status' => true],
            ['name' => 'Contact', 'slug' => 'contact', 'description' => 'Contact menu', 'status' => true],
        ];

        foreach ($menuGroups as $groupData) {
            MenuGroup::firstOrCreate(
                ['slug' => $groupData['slug']],
                $groupData
            );
        }
    }
}
