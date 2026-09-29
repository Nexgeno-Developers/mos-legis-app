<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Support\Permissions;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * SOW A.10 — the three roles plus the admin permission catalogue.
 * Uses Spatie's own models so its permission cache stays consistent.
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (Permissions::all() as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $superadmin = Role::findOrCreate(RoleName::Superadmin->value, 'web');
        $superadmin->syncPermissions(Permissions::all());

        $reviewer = Role::findOrCreate(RoleName::Reviewer->value, 'web');
        if ($reviewer->permissions()->doesntExist()) {
            $reviewer->syncPermissions(Permissions::REVIEWER_DEFAULTS);
        }

        // Authors act only on the public site/portal; access is enforced by policies, not admin permissions.
        Role::findOrCreate(RoleName::Author->value, 'web');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
