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
            ['name' => 'Footer', 'slug' => 'footer', 'description' => 'Footer menu', 'status' => true],
        ];

        foreach ($menuGroups as $groupData) {
            MenuGroup::firstOrCreate(
                ['slug' => $groupData['slug']],
                $groupData
            );
        }
    }
}
