<?php

namespace App\Http\Controllers\Auth;

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
 * SOW B.01 — sign in or sign up with Google (ORCID is used only to connect an iD, see OrcidController).
 * Existing authors are signed in (an account with the same email is linked). A new author finishes the
 * registration form (author category, institution, optional ORCID iD); Google has already verified the
 * email, so no OTP is needed.
 */
class SocialLoginController extends Controller
{
    public const SESSION_KEY = 'pending_registration';

    public function redirect(SocialProvider $provider): SymfonyRedirect|RedirectResponse
    {
        if (! config("services.{$provider->value}.client_id")) {
            return redirect()->route('login')->withErrors(['email' => 'Google sign-in is not configured yet.']);
        }

        return Socialite::driver($provider->value)->redirect();
    }

    public function callback(Request $request, SocialProvider $provider): RedirectResponse
    {
        if ($request->filled('error')) {
            // The user pressed "Cancel" on Google's page.
            return redirect()->route('login')->withErrors(['email' => 'Google sign-in was cancelled.']);
        }

        try {
            $identity = Socialite::driver($provider->value)->user();
        } catch (Throwable $e) {
            Log::warning("{$provider->value} sign-in failed", ['error' => $e->getMessage()]);

            return redirect()->route('login')->withErrors(['email' => 'Google sign-in failed. Please try again.']);
        }

        // Google only returns addresses it has verified.
        $email = $identity->getEmail() ? strtolower($identity->getEmail()) : null;
        $linked = UserSocialAccount::with('user')->where('provider', $provider)->where('provider_user_id', $identity->getId())->first();
        $user = $linked?->user ?? ($email ? User::firstWhere('email', $email) : null);

        if (! $user) {
            if (! $email) {
                return redirect()->route('register')->withErrors(['email' => 'Google did not share an email address. Please register with your email instead.']);
            }

            $request->session()->put(self::SESSION_KEY, [
                'provider' => $provider->value,
                'provider_user_id' => $identity->getId(),
                'name' => $identity->getName(),
                'email' => $email,
                'email_verified' => true,
            ]);

            return redirect()->route('register')->with('status', 'Almost done — add your author category and institution to create your account.');
        }

        if (! $user->isAuthor() || ! $user->isActive()) {
            return redirect()->route('login')->withErrors(['email' => 'This account cannot sign in on the website.']);
        }

        if (! $linked) {
            $user->socialAccounts()->firstOrCreate(['provider' => $provider, 'provider_user_id' => $identity->getId()]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }
}
