<?php

namespace Tests\Feature\Admin;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    #[Test]
    public function admin_can_view_and_update_profile(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->get(route('admin.profile.edit'))->assertOk()->assertSee($admin->email);

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Priya Nair',
            'email' => 'priya@moslegis.com',
            'phone' => '9876543210',
        ])->assertSessionHasNoErrors()->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'name' => 'Priya Nair', 'email' => 'priya@moslegis.com']);
    }

    #[Test]
    public function changing_password_requires_the_current_password(): void
    {
        $admin = $this->superadmin();

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'current_password' => 'wrong',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => $admin->name,
            'email' => $admin->email,
            'current_password' => 'password',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(password_verify('new-password-123', $admin->fresh()->password));
    }

    #[Test]
    public function reviewer_can_edit_their_own_profile(): void
    {
        $reviewer = $this->reviewer();

        $this->actingAs($reviewer)->get(route('admin.profile.edit'))->assertOk();
    }
}
