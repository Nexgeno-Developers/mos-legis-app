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
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

/**
 * SOW B.01 — sign in or register with Google (Gmail) or ORCID.
 * Works for both sign-in and sign-up. When the provider returns no verified email
 * (common with ORCID, where emails are private by default) the user confirms one by OTP.
 */
class SocialLoginController extends Controller
{
    public function redirect(SocialProvider $provider): SymfonyRedirect|RedirectResponse
    {
        if (! config("services.{$provider->value}.client_id")) {
            return redirect()->route('login')->withErrors(['email' => ucfirst($provider->value).' sign-in is not configured yet.']);
        }

        return Socialite::driver($provider->value)->redirect();
    }

    public function callback(Request $request, SocialProvider $provider, CreateAuthor $createAuthor): RedirectResponse
    {
        if ($request->filled('error')) {
            // The user pressed "Deny" / cancelled on the provider's page.
            return redirect()->route('login')->withErrors(['email' => ucfirst($provider->value).' sign-in was cancelled.']);
        }

        try {
            $identity = Socialite::driver($provider->value)->user();
        } catch (Throwable $e) {
            Log::warning("{$provider->value} sign-in failed", ['error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['email' => ucfirst($provider->value).' sign-in failed. Please try again.']);
        }

        $isOrcid = $provider === SocialProvider::Orcid;
        $linked = UserSocialAccount::with('user')->where('provider', $provider)->where('provider_user_id', $identity->getId())->first();
        // Google and ORCID only return addresses they have verified.
        $email = $identity->getEmail() ? strtolower($identity->getEmail()) : null;
        $user = $linked?->user ?? ($email ? User::firstWhere('email', $email) : null);

        if ($user) {
            if (! $user->isAuthor() || ! $user->isActive()) {
                return redirect()->route('login')->withErrors(['email' => 'This account cannot sign in on the website.']);
            }

            if (! $linked) {
                $user->socialAccounts()->firstOrCreate(['provider' => $provider, 'provider_user_id' => $identity->getId()]);
            }
        } elseif ($email) {
            // Verified email from the provider: create the author account straight away.
            $user = $createAuthor->handle([
                'name' => $identity->getName() ?: $email,
                'email' => $email,
                'orcid' => $isOrcid ? $identity->getId() : null,
            ], $provider, $identity->getId());
        } else {
            $request->session()->put('pending_registration', [
                'provider' => $provider->value,
                'provider_user_id' => $identity->getId(),
                'name' => $identity->getName(),
                'orcid' => $provider === SocialProvider::Orcid ? $identity->getId() : null,
            ]);

            return redirect()->route('register')->with('status', 'Almost there — confirm your email address to finish creating your account.');
        }

        // Keep the ORCID iD on the author profile (shown on submissions and the archive).
        if ($isOrcid && ! $user->authorProfile?->orcid) {
            $user->authorProfile()->updateOrCreate([], ['orcid' => $identity->getId()]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }
}
