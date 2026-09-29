<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleManagementTest extends TestCase
{
    #[Test]
    public function superadmin_can_create_a_custom_role_with_permissions(): void
    {
        $this->actingAs($this->superadmin())->post(route('admin.roles.store'), [
            'name' => 'Copy Editor',
            'permissions' => ['blogs.view', 'blogs.edit'],
        ])->assertRedirect(route('admin.roles.index'));

        $role = Role::findByName('copy editor');
        $this->assertTrue($role->hasPermissionTo('blogs.edit'));
        $this->assertFalse($role->hasPermissionTo('blogs.delete'));
    }

    #[Test]
    public function reviewer_permissions_granted_by_superadmin_take_effect(): void
    {
        $reviewer = $this->reviewer();
        $this->actingAs($reviewer)->get(route('admin.users.index'))->assertForbidden();

        $this->actingAs($this->superadmin())->put(route('admin.roles.update', Role::findByName('reviewer')), [
            'name' => 'reviewer',
            'permissions' => ['dashboard.view', 'users.view'],
        ])->assertRedirect();

        $this->actingAs($reviewer->fresh())->get(route('admin.users.index'))->assertOk();
    }

    #[Test]
    public function custom_role_users_can_access_the_admin_panel(): void
    {
        $role = Role::create(['name' => 'editor', 'guard_name' => 'web']);
        $role->givePermissionTo('dashboard.view');
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)->get(route('admin.profile.edit'))->assertOk();
    }

    #[Test]
    public function system_roles_cannot_be_deleted(): void
    {
        $this->actingAs($this->superadmin())
            ->delete(route('admin.roles.destroy', Role::findByName('reviewer')))
            ->assertSessionHas('error');

        $this->assertNotNull(Role::findByName('reviewer'));
    }

    #[Test]
    public function superadmin_role_permissions_cannot_be_changed(): void
    {
        $this->actingAs($this->superadmin())
            ->put(route('admin.roles.update', Role::findByName('superadmin')), ['name' => 'superadmin', 'permissions' => []])
            ->assertForbidden();
    }

    #[Test]
    public function unknown_permissions_are_rejected(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('admin.roles.store'), ['name' => 'x', 'permissions' => ['everything.delete']])
            ->assertSessionHasErrors('permissions.0');
    }
}
