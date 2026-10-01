<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

/**
 * "Connect your ORCID iD": the author signs in to ORCID (usually in a pop-up) and we receive
 * their authenticated iD, so it is never typed by hand. ORCID is not a way to log in here.
 *
 * - During registration (guest) the verified iD is held in the session until the account is created.
 * - In the author profile it is saved straight away and cannot be changed afterwards.
 */
class OrcidController extends Controller
{
    public const SESSION_KEY = 'verified_orcid';

    public function redirect(Request $request): SymfonyRedirect|RedirectResponse
    {
        $request->session()->put('orcid_popup', $request->boolean('popup'));

        if (! config('services.orcid.client_id')) {
            return $this->finish($request, false, 'ORCID connection is not configured yet.');
        }

        if ($request->user()?->authorProfile?->orcid) {
            return $this->finish($request, false, 'Your ORCID iD is already linked.');
        }

        return Socialite::driver('orcid')->redirect();
    }

    public function callback(Request $request): View|RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->finish($request, false, 'ORCID connection was cancelled.');
        }

        try {
            $identity = Socialite::driver('orcid')->user();
        } catch (Throwable $e) {
            Log::warning('ORCID connect failed', ['error' => $e->getMessage()]);

            return $this->finish($request, false, 'We could not connect to ORCID. Please try again.');
        }

        $orcid = (string) $identity->getId();
        $user = $request->user();

        if (AuthorProfile::where('orcid', $orcid)->when($user, fn ($q) => $q->where('user_id', '!=', $user->id))->exists()) {
            return $this->finish($request, false, 'This ORCID iD is already linked to another account. Sign in to that account instead, or write to the editorial office.');
        }

        if ($user) {
            if ($user->authorProfile?->orcid) {
                return $this->finish($request, false, 'Your ORCID iD is already linked and cannot be changed.');
            }

            $user->authorProfile()->updateOrCreate([], ['orcid' => $orcid]);
            activity()->log('Authentication', 'Linked ORCID iD to profile', $user, ['orcid' => $orcid]);

            return $this->finish($request, true, 'Your ORCID iD is now linked to your profile.', $orcid, $identity->getName());
        }

        $request->session()->put(self::SESSION_KEY, ['id' => $orcid, 'name' => $identity->getName()]);

        return $this->finish($request, true, 'ORCID iD connected.', $orcid, $identity->getName());
    }

    /** Remove the ORCID iD connected during registration (not possible once it is on a profile). */
    public function forget(Request $request): JsonResponse|RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return $request->expectsJson() ? response()->json(['ok' => true]) : back();
    }

    /**
     * Pop-up: hand the result to the page that opened it and close. Full-page fallback:
     * return to the registration form or the profile with a message.
     */
    private function finish(Request $request, bool $ok, string $message, ?string $orcid = null, ?string $name = null): View|RedirectResponse
    {
        $returnTo = $request->user() ? route('account.profile.edit') : route('register');

        if ($request->session()->pull('orcid_popup')) {
            return view('auth.orcid-popup', [
                'payload' => ['type' => 'orcid-connect', 'ok' => $ok, 'message' => $message, 'orcid' => $orcid, 'name' => $name],
                'returnTo' => $returnTo,
            ]);
        }

        return redirect($returnTo)->with($ok ? 'success' : 'error', $message);
    }
}
