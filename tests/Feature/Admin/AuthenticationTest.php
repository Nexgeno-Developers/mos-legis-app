<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    #[Test]
    public function login_page_renders(): void
    {
        $this->get(route('admin.login'))->assertOk()->assertSee('Sign in');
    }

    #[Test]
    public function superadmin_can_sign_in(): void
    {
        $admin = $this->superadmin();

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $admin->id, 'module' => 'Authentication']);
    }

    #[Test]
    public function reviewer_can_sign_in(): void
    {
        $reviewer = $this->reviewer();

        $this->post(route('admin.login.store'), ['email' => $reviewer->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    #[Test]
    public function authors_cannot_sign_in_to_the_admin_panel(): void
    {
        $author = $this->author();

        $this->post(route('admin.login.store'), ['email' => $author->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function inactive_admins_cannot_sign_in(): void
    {
        $admin = User::factory()->inactive()->superadmin()->create();

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function wrong_password_is_rejected_and_rate_limited(): void
    {
        $admin = $this->superadmin();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'wrong'])
                ->assertSessionHasErrors('email');
        }

        $this->post(route('admin.login.store'), ['email' => $admin->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function guests_are_redirected_to_admin_login(): void
    {
        $this->get(route('admin.profile.edit'))->assertRedirect(route('admin.login'));
    }

    #[Test]
    public function signed_in_authors_are_bounced_from_the_admin_panel(): void
    {
        $this->actingAs($this->author())
            ->get(route('admin.profile.edit'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    #[Test]
    public function admin_can_sign_out(): void
    {
        $this->actingAs($this->superadmin())
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    #[Test]
    public function password_reset_link_can_be_requested_and_used(): void
    {
        Notification::fake();
        $admin = $this->superadmin();

        $this->post(route('admin.password.email'), ['email' => $admin->email])->assertSessionHas('status');

        Notification::assertSentTo($admin, ResetPassword::class, function (ResetPassword $notification) use ($admin) {
            $this->post(route('password.update'), [
                'token' => $notification->token,
                'email' => $admin->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])->assertRedirect(route('admin.login'));

            return true;
        });

        $this->assertTrue(password_verify('new-password-123', $admin->fresh()->password));
    }

    #[Test]
    public function unknown_email_gets_the_same_reset_response(): void
    {
        $this->post(route('admin.password.email'), ['email' => 'nobody@example.com'])
            ->assertSessionHas('status');
    }
}
