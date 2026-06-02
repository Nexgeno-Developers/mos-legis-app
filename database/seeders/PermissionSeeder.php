<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed permissions used by backend (sidebar modules + users & roles)
        $permissions = [
            // Dashboard
            'dashboard view',

            // Company
            'companies edit',

            // Pages (CMS)
            'pages view',
            'pages create',
            'pages edit',
            'pages delete',

            // Media uploads
            'uploads view',
            'uploads create',
            'uploads edit',
            'uploads delete',

            // Form submissions
            'forms view',
            'forms delete',

            // Menus
            'menus view',
            'menus create',
            'menus edit',
            'menus delete',

            // Visitors
            'visitors view',
            'visitors delete',

            // User management
            'users view',
            'users create',
            'users edit',
            'users delete',

            // Role management
            'roles view',
            'roles create',
            'roles edit',
            'roles delete',

            // SEO management
            'seo-meta view',
            'seo-meta create',
            'seo-meta edit',
            'seo-meta delete',

            // SEO settings (site-level)
            'seo-settings view',
            'seo-settings edit',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web'],
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }

        // Attach all permissions to superadmin role
        $superAdminRole = Role::where('name', 'superadmin')->first();
        if ($superAdminRole) {
            $superAdminRole->syncPermissions($permissions);
        }
    }
}
