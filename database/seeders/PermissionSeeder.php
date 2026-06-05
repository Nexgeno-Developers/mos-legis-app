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

            // Companies
            'companies view',
            'companies edit',

            // Pages (CMS)
            'pages view',
            'pages create',
            'pages edit',
            'pages delete',

            // Media uploads
            'uploads view',
            'uploads create',
            'uploads delete',

            // Menus
            'menus view',
            'menus create',
            'menus edit',
            'menus delete',

            // Visitors
            'visitors view',
            'visitors delete',

            // Activity logs
            'activity-logs view',
            'activity-logs delete',

            // User management
            'users view',
            'users create',
            'users edit',
            'users delete',

            // Customers
            'customers view',
            'customers create',
            'customers edit',
            'customers delete',

            // Role management
            'roles view',
            'roles create',
            'roles edit',
            'roles delete',

            // Properties
            'properties view',
            'properties create',
            'properties edit',
            'properties delete',

            // Cabins
            'cabins view',
            'cabins create',
            'cabins edit',
            'cabins delete',

            // Seats
            'seats view',
            'seats create',
            'seats edit',
            'seats delete',

            // Bookings
            'bookings view',
            'bookings create',
            'bookings edit',
            'bookings delete',
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
