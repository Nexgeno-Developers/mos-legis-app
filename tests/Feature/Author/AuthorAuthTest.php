<?php

namespace Tests\Feature\Author;

use App\Mail\OtpCodeMail;
use App\Models\AuthorCategory;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthorAuthTest extends TestCase
{
    #[Test]
    public function registration_requires_email_otp_before_the_account_exists(): void
    {
        Mail::fake();
        $category = AuthorCategory::factory()->create();

        $this->post(route('register.store'), [
            'name' => 'Ananya Iyer',
            'email' => 'ananya@example.com',
            'phone' => '9876543210',
            'author_category_id' => $category->id,
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
            'terms' => '1',
        ])->assertRedirect(route('register.verify'));

        $this->assertDatabaseMissing('users', ['email' => 'ananya@example.com']);

        $code = null;
        Mail::assertSent(OtpCodeMail::class, function (OtpCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return $mail->hasTo('ananya@example.com');
        });

        $this->post(route('register.verify.store'), ['otp' => '000000'])->assertSessionHasErrors('otp');
        $this->post(route('register.verify.store'), ['otp' => $code])->assertRedirect(route('account.profile.edit'));

        $user = User::where('email', 'ananya@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->isAuthor());
        $this->assertNotNull($user->email_verified_at);
        $this->assertSame($category->id, $user->authorProfile->author_category_id);
    }

    #[Test]
    public function otp_locks_after_five_wrong_attempts(): void
    {
        Mail::fake();
        $this->post(route('register.store'), [
            'name' => 'X', 'email' => 'x@example.com', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1', 'terms' => '1',
        ]);
        $code = null;
        Mail::assertSent(OtpCodeMail::class, function ($mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        foreach (range(1, 5) as $i) {
            $this->post(route('register.verify.store'), ['otp' => $code === '111111' ? '222222' : '111111']);
        }

        $this->post(route('register.verify.store'), ['otp' => $code])->assertSessionHasErrors('otp');
        $this->assertGuest();
    }

    #[Test]
    public function author_signs_in_on_the_website_but_admins_cannot(): void
    {
        $author = $this->author();
        $admin = $this->superadmin();

        $this->post(route('login.store'), ['email' => $admin->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login.store'), ['email' => $author->email, 'password' => 'password'])->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticatedAs($author);
    }

    #[Test]
    public function portal_requires_an_author(): void
    {
        $this->get(route('account.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($this->superadmin())->get(route('account.dashboard'))->assertRedirect(route('admin.dashboard'));
        $this->actingAs($this->author())->get(route('account.dashboard'))->assertOk();
    }

    #[Test]
    public function orcid_requests_only_the_public_authenticate_scope(): void
    {
        config(['services.orcid' => [
            'client_id' => 'APP-TEST', 'client_secret' => 'secret',
            'redirect' => 'http://127.0.0.1:8000/auth/orcid/callback', 'environment' => 'sandbox',
        ]]);

        $location = $this->get(route('social.redirect', 'orcid'))->assertRedirect()->headers->get('Location');

        $this->assertStringStartsWith('https://sandbox.orcid.org/oauth/authorize', $location);
        $this->assertStringContainsString('scope=%2Fauthenticate&', $location);
        $this->assertStringNotContainsString('read-limited', $location);
    }

    #[Test]
    public function unconfigured_social_login_is_reported(): void
    {
        config(['services.google.client_id' => null]);

        $this->get(route('social.redirect', 'google'))->assertRedirect(route('login'))->assertSessionHasErrors('email');
    }
}
