<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateAuthor;
use App\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserSocialAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

/**
 * SOW B.01 — sign in or register with Google (Gmail) or ORCID.
 * ORCID usually withholds the email address, so those users confirm an email via OTP first.
 */
class SocialLoginController extends Controller
{
    public function redirect(SocialProvider $provider): SymfonyRedirect|RedirectResponse
    {
        if (! config("services.{$provider->value}.client_id")) {
            return redirect()->route('login')->withErrors(['email' => ucfirst($provider->value).' sign-in is not configured yet.']);
        }

        $driver = Socialite::driver($provider->value);

        return $provider === SocialProvider::Orcid ? $driver->scopes(['/authenticate'])->redirect() : $driver->redirect();
    }

    public function callback(Request $request, SocialProvider $provider, CreateAuthor $createAuthor): RedirectResponse
    {
        try {
            $identity = Socialite::driver($provider->value)->user();
        } catch (Throwable) {
            return redirect()->route('login')->withErrors(['email' => 'Sign-in was cancelled or failed. Please try again.']);
        }

        $linked = UserSocialAccount::with('user')->where('provider', $provider)->where('provider_user_id', $identity->getId())->first();
        $email = $identity->getEmail() ? strtolower($identity->getEmail()) : null;
        $user = $linked?->user ?? ($email ? User::firstWhere('email', $email) : null);

        if ($user) {
            if (! $user->isAuthor() || ! $user->isActive()) {
                return redirect()->route('login')->withErrors(['email' => 'This account cannot sign in on the website.']);
            }

            if (! $linked) {
                $user->socialAccounts()->firstOrCreate(['provider' => $provider, 'provider_user_id' => $identity->getId()]);
            }
        } elseif ($email && $provider === SocialProvider::Google) {
            // Google verifies the address, so the account can be created straight away.
            $user = $createAuthor->handle(['name' => $identity->getName() ?: $email, 'email' => $email], $provider, $identity->getId());
        } else {
            $request->session()->put('pending_registration', [
                'provider' => $provider->value,
                'provider_user_id' => $identity->getId(),
                'name' => $identity->getName(),
                'orcid' => $provider === SocialProvider::Orcid ? $identity->getId() : null,
            ]);

            return redirect()->route('register')->with('status', 'Almost there — confirm your email address to finish creating your account.');
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }
}
