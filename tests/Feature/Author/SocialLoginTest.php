<?php

namespace Tests\Feature\Author;

use App\Mail\OtpCodeMail;
use App\Models\User;
use App\Socialite\OrcidProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use ReflectionMethod;
use SocialiteProviders\Manager\Config;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    private function fakeProvider(string $id, ?string $email, string $name = 'Test Scholar'): void
    {
        $identity = (new SocialiteUser)->map(['id' => $id, 'name' => $name, 'email' => $email]);
        $driver = Mockery::mock();
        $driver->shouldReceive('user')->andReturn($identity);
        Socialite::shouldReceive('driver')->andReturn($driver);
    }

    #[Test]
    public function orcid_without_public_email_completes_signup_with_otp(): void
    {
        Mail::fake();
        $this->fakeProvider('0000-0002-1825-0097', null, 'Josiah Carberry');

        $this->get(route('social.callback', 'orcid').'?code=abc')->assertRedirect(route('register'));
        $this->get(route('register'))->assertOk()->assertSee('Connected')->assertSee('0000-0002-1825-0097')->assertDontSee('Sign up with Google');

        $this->post(route('register.store'), ['name' => 'Josiah Carberry', 'email' => 'josiah@example.com', 'terms' => '1'])
            ->assertRedirect(route('register.verify'));

        $code = null;
        Mail::assertSent(OtpCodeMail::class, function (OtpCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });
        $this->post(route('register.verify.store'), ['otp' => $code])->assertRedirect();

        $user = User::where('email', 'josiah@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->password);
        $this->assertSame('0000-0002-1825-0097', $user->authorProfile->orcid);
        $this->assertDatabaseHas('user_social_accounts', ['user_id' => $user->id, 'provider' => 'orcid', 'provider_user_id' => '0000-0002-1825-0097']);
    }

    #[Test]
    public function orcid_with_verified_email_creates_the_account_immediately(): void
    {
        $this->fakeProvider('0000-0001-5109-3700', 'scholar@uni.edu');

        $this->get(route('social.callback', 'orcid').'?code=abc')->assertRedirect(route('account.dashboard'));

        $user = User::where('email', 'scholar@uni.edu')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->isAuthor());
        $this->assertSame('0000-0001-5109-3700', $user->authorProfile->orcid);
    }

    #[Test]
    public function linked_account_signs_in_and_existing_email_is_linked(): void
    {
        $author = $this->author(['email' => 'ananya@example.com']);

        $this->fakeProvider('google-123', 'ananya@example.com');
        $this->get(route('social.callback', 'google').'?code=abc')->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticatedAs($author);
        $this->assertDatabaseHas('user_social_accounts', ['user_id' => $author->id, 'provider' => 'google']);

        auth()->logout();

        // ORCID iD links to the same author even though ORCID returns no email.
        $author->socialAccounts()->create(['provider' => 'orcid', 'provider_user_id' => '0000-0003-0000-0001']);
        $this->fakeProvider('0000-0003-0000-0001', null);
        $this->get(route('social.callback', 'orcid').'?code=abc')->assertRedirect(route('account.dashboard'));
        $this->assertAuthenticatedAs($author);
        $this->assertSame(1, User::count() - User::role(['superadmin', 'reviewer'])->count());
    }

    #[Test]
    public function google_signup_creates_a_verified_author(): void
    {
        $this->fakeProvider('google-999', 'new.author@gmail.com', 'New Author');

        $this->get(route('social.callback', 'google').'?code=abc')->assertRedirect(route('account.dashboard'));

        $user = User::where('email', 'new.author@gmail.com')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->isAuthor());
    }

    #[Test]
    public function staff_accounts_cannot_use_website_social_login(): void
    {
        $admin = $this->superadmin(['email' => 'boss@moslegis.com']);
        $this->fakeProvider('google-admin', 'boss@moslegis.com');

        $this->get(route('social.callback', 'google').'?code=abc')->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function cancelling_on_the_provider_page_returns_to_login(): void
    {
        $this->get(route('social.callback', 'orcid').'?error=access_denied')->assertRedirect(route('login'))->assertSessionHasErrors('email');
    }

    #[Test]
    public function register_and_login_pages_offer_both_providers(): void
    {
        $this->get(route('register'))->assertOk()->assertSee('Sign up with Google')->assertSee('Sign up with ORCID');
        $this->get(route('login'))->assertOk()->assertSee('Sign in with Google')->assertSee('Sign in with ORCID');
    }

    #[Test]
    public function start_over_clears_a_pending_social_signup(): void
    {
        $this->withSession(['pending_registration' => ['provider' => 'orcid', 'provider_user_id' => 'x', 'name' => 'X']])
            ->post(route('register.reset'))->assertRedirect(route('register'));

        $this->get(route('register'))->assertSee('Sign up with ORCID');
    }

    #[Test]
    public function orcid_provider_reads_v3_record_and_survives_a_private_profile(): void
    {
        $provider = new OrcidProvider(Request::create('/'), 'APP-TEST', 'secret', 'http://127.0.0.1:8000/auth/orcid/callback');
        $provider->setConfig(new Config('APP-TEST', 'secret', 'http://127.0.0.1:8000/auth/orcid/callback', ['environment' => 'sandbox']));
        $history = [];
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode([
                'orcid-identifier' => ['path' => '0000-0002-1825-0097'],
                'person' => [
                    'name' => ['given-names' => ['value' => 'Josiah'], 'family-name' => ['value' => 'Carberry']],
                    'emails' => ['email' => [['email' => 'josiah@brown.edu', 'primary' => true, 'verified' => true]]],
                ],
            ])),
            new Response(404),
        ]));
        $stack->push(Middleware::history($history));
        $provider->setHttpClient(new Client(['handler' => $stack]));

        $getUser = new ReflectionMethod($provider, 'getUserByToken');
        $map = new ReflectionMethod($provider, 'mapUserToObject');

        $user = $map->invoke($provider, $getUser->invoke($provider, ['orcid' => '0000-0002-1825-0097', 'name' => 'Josiah Carberry', 'access_token' => 't']));
        $this->assertSame('0000-0002-1825-0097', $user->getId());
        $this->assertSame('Josiah Carberry', $user->getName());
        $this->assertSame('josiah@brown.edu', $user->getEmail());
        $this->assertSame('https://pub.sandbox.orcid.org/v3.0/0000-0002-1825-0097/record', (string) $history[0]['request']->getUri());

        // Record unavailable (private/404): iD and name still come from the token response.
        $private = $map->invoke($provider, $getUser->invoke($provider, ['orcid' => '0000-0002-1825-0097', 'name' => 'Josiah Carberry', 'access_token' => 't']));
        $this->assertSame('0000-0002-1825-0097', $private->getId());
        $this->assertSame('Josiah Carberry', $private->getName());
        $this->assertNull($private->getEmail());
    }
}
