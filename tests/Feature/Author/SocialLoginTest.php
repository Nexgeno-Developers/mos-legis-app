<?php

namespace Tests\Feature\Author;

use App\Mail\OtpCodeMail;
use App\Models\AuthorCategory;
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

/**
 * Google sign-in / sign-up, and "Connect your ORCID iD" (ORCID is not a login method).
 */
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
    public function new_google_user_finishes_registration_without_an_otp(): void
    {
        Mail::fake();
        $category = AuthorCategory::factory()->create();
        $this->fakeProvider('google-777', 'new.scholar@gmail.com', 'New Scholar');

        $this->get(route('social.callback', 'google').'?code=abc')->assertRedirect(route('register'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'new.scholar@gmail.com']);

        $this->get(route('register'))->assertOk()->assertSee('Verified by Google')->assertSee('new.scholar@gmail.com')->assertSee('Create my account');

        // The email cannot be swapped: Google verified it.
        $this->post(route('register.store'), [
            'name' => 'New Scholar', 'email' => 'someone.else@example.com', 'author_category_id' => $category->id,
            'institution' => 'NALSAR Hyderabad', 'terms' => '1',
        ])->assertRedirect(route('account.dashboard'));

        Mail::assertNothingSent();
        $user = User::where('email', 'new.scholar@gmail.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->password);
        $this->assertSame('NALSAR Hyderabad', $user->authorProfile->institution);
        $this->assertSame($category->id, $user->authorProfile->author_category_id);
        $this->assertDatabaseHas('user_social_accounts', ['user_id' => $user->id, 'provider' => 'google', 'provider_user_id' => 'google-777']);
        $this->assertDatabaseMissing('users', ['email' => 'someone.else@example.com']);
    }

    #[Test]
    public function existing_author_signs_in_with_google_and_the_account_is_linked(): void
    {
        $author = $this->author(['email' => 'ananya@example.com']);
        $this->fakeProvider('google-123', 'ananya@example.com');

        $this->get(route('social.callback', 'google').'?code=abc')->assertRedirect(route('account.dashboard'));

        $this->assertAuthenticatedAs($author);
        $this->assertDatabaseHas('user_social_accounts', ['user_id' => $author->id, 'provider' => 'google', 'provider_user_id' => 'google-123']);
    }

    #[Test]
    public function staff_accounts_cannot_use_website_social_login(): void
    {
        $this->superadmin(['email' => 'boss@moslegis.com']);
        $this->fakeProvider('google-admin', 'boss@moslegis.com');

        $this->get(route('social.callback', 'google').'?code=abc')->assertRedirect(route('login'))->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[Test]
    public function cancelling_on_google_returns_to_login(): void
    {
        $this->get(route('social.callback', 'google').'?error=access_denied')->assertRedirect(route('login'))->assertSessionHasErrors('email');
    }

    #[Test]
    public function orcid_is_not_a_login_method_but_can_be_connected_when_registering(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Sign in with Google')->assertDontSee('Sign in with ORCID');
        $this->get(route('register'))->assertOk()->assertSee('Sign up with Google')->assertDontSee('Sign up with ORCID')
            ->assertSee('Connect your ORCID iD');
    }

    #[Test]
    public function orcid_connected_during_registration_is_saved_with_the_new_account(): void
    {
        Mail::fake();
        $category = AuthorCategory::factory()->create();
        $this->fakeProvider('0000-0002-1825-0097', null, 'JOSIAH CARBERRY');

        $this->get(route('orcid.callback').'?code=abc')->assertRedirect(route('register'))->assertSessionHas('success');
        $this->assertSame('0000-0002-1825-0097', session('verified_orcid.id'));
        $this->get(route('register'))->assertSee('0000-0002-1825-0097');

        // A typed ORCID value is ignored; only the verified one from the session counts.
        $this->post(route('register.store'), [
            'name' => 'Josiah Carberry', 'email' => 'josiah@example.com', 'author_category_id' => $category->id, 'institution' => 'Brown University',
            'orcid' => '9999-9999-9999-9999', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1', 'terms' => '1',
        ])->assertRedirect(route('register.verify'));

        $code = null;
        Mail::assertSent(OtpCodeMail::class, function (OtpCodeMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });
        $this->post(route('register.verify.store'), ['otp' => $code])->assertRedirect(route('account.dashboard'));

        $user = User::where('email', 'josiah@example.com')->firstOrFail();
        $this->assertSame('0000-0002-1825-0097', $user->authorProfile->orcid);
        $this->assertNull(session('verified_orcid'));
    }

    #[Test]
    public function orcid_popup_hands_the_verified_id_back_to_the_form(): void
    {
        $this->fakeProvider('0000-0002-1825-0097', null, 'Josiah Carberry');

        $this->withSession(['orcid_popup' => true])->get(route('orcid.callback').'?code=abc')
            ->assertOk()->assertSee('postMessage', false)->assertSee('0000-0002-1825-0097');
    }

    #[Test]
    public function an_orcid_connected_during_registration_can_be_removed(): void
    {
        $this->withSession(['verified_orcid' => ['id' => '0000-0002-1825-0097', 'name' => 'X']])
            ->postJson(route('orcid.forget'))->assertOk();

        $this->assertNull(session('verified_orcid'));
    }

    #[Test]
    public function author_links_orcid_from_the_profile_once_and_it_is_then_locked(): void
    {
        $author = $this->author();

        $this->fakeProvider('0000-0001-5109-3700', null);
        $this->actingAs($author)->get(route('orcid.callback').'?code=abc')->assertRedirect(route('account.profile.edit'))->assertSessionHas('success');
        $this->assertSame('0000-0001-5109-3700', $author->fresh()->authorProfile->orcid);

        $this->actingAs($author)->get(route('account.profile.edit'))->assertOk()->assertSee('Locked');

        // A second ORCID cannot replace it.
        $this->fakeProvider('0000-0003-0000-0001', null);
        $this->actingAs($author)->get(route('orcid.callback').'?code=abc')->assertSessionHas('error');
        $this->assertSame('0000-0001-5109-3700', $author->fresh()->authorProfile->orcid);
    }

    #[Test]
    public function an_orcid_already_on_another_account_is_refused(): void
    {
        $this->author()->authorProfile()->update(['orcid' => '0000-0001-5109-3700']);
        $author = $this->author();

        $this->fakeProvider('0000-0001-5109-3700', null);
        $this->actingAs($author)->get(route('orcid.callback').'?code=abc')->assertSessionHas('error');
        $this->assertNull($author->fresh()->authorProfile->orcid);

        auth()->logout();
        $this->get(route('orcid.callback').'?code=abc')->assertSessionHas('error');
        $this->assertNull(session('verified_orcid'));
    }

    #[Test]
    public function cancelling_on_orcid_returns_with_a_message(): void
    {
        $this->get(route('orcid.callback').'?error=access_denied')->assertRedirect(route('register'))->assertSessionHas('error');
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
