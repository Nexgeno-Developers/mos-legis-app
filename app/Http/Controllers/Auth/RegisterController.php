<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateAuthor;
use App\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use App\Models\AuthorCategory;
use App\Models\AuthorProfile;
use App\Models\User;
use App\Services\Auth\OtpService;
use App\Support\PhoneNumbers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * SOW B.01 — registration with email OTP verification. The account is only
 * created once the code is confirmed; until then the details live in the session.
 * Also completes Google sign-ups (email already verified by Google, so no OTP). The ORCID iD is
 * optional and only accepted when connected through ORCID (see OrcidController).
 */
class RegisterController extends Controller
{
    private const SESSION_KEY = 'pending_registration';

    public function __construct(private readonly OtpService $otp) {}

    public function create(Request $request): View
    {
        $pending = $request->session()->get(self::SESSION_KEY, []);

        return view('auth.register', [
            'authorCategories' => AuthorCategory::active()->orderBy('name')->pluck('name', 'id'),
            'social' => isset($pending['provider']) ? $pending : null,
            'orcid' => $request->session()->get(OrcidController::SESSION_KEY),
        ]);
    }

    public function store(Request $request, CreateAuthor $createAuthor): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY, []);
        // Google sign-up: the email comes from Google (already verified) and cannot be edited.
        $verifiedEmail = ! empty($pending['email_verified']) ? $pending['email'] : null;
        if ($verifiedEmail) {
            $request->merge(['email' => $verifiedEmail]);
        }

        $request->merge(['phone' => PhoneNumbers::normalize($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'phone' => PhoneNumbers::rules(),
            'author_category_id' => ['required', 'integer', Rule::exists('manuscript_author_categories', 'id')->where('status', 'Active')],
            'institution' => ['required', 'string', 'max:190'],
            'password' => [$verifiedEmail ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ], [
            'email.unique' => 'An account with this email already exists. Please sign in instead.',
            'author_category_id.required' => 'Choose your author category.',
            'institution.required' => 'Enter your institution or organisation.',
        ]);

        $orcid = $request->session()->get(OrcidController::SESSION_KEY.'.id');
        if ($orcid && AuthorProfile::where('orcid', $orcid)->exists()) {
            $request->session()->forget(OrcidController::SESSION_KEY);

            return back()->withInput()->withErrors(['orcid' => 'This ORCID iD is already linked to another account.']);
        }

        $details = array_merge($pending, [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'author_category_id' => (int) $data['author_category_id'],
            'institution' => $data['institution'],
            'orcid' => $orcid,
            'password' => ! empty($data['password']) ? Hash::make($data['password']) : null,
        ]);

        if ($verifiedEmail) {
            return $this->complete($request, $createAuthor, $details);
        }

        $request->session()->put(self::SESSION_KEY, $details);
        $this->otp->issue($details['email']);

        return redirect()->route('register.verify')->with('status', "We've emailed a 6-digit code to {$details['email']}.");
    }

    public function verifyForm(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has(self::SESSION_KEY.'.email')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp', ['email' => $request->session()->get(self::SESSION_KEY.'.email')]);
    }

    public function verify(Request $request, CreateAuthor $createAuthor): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);
        abort_unless(isset($pending['email']), 419);

        $request->validate(['otp' => ['required', 'digits:6']]);

        if (! $this->otp->verify($pending['email'], $request->string('otp'))) {
            return back()->withErrors(['otp' => 'That code is invalid or has expired.']);
        }

        if (User::where('email', $pending['email'])->exists()) {
            $request->session()->forget(self::SESSION_KEY);

            return redirect()->route('login')->withErrors(['email' => 'An account with this email already exists. Please sign in.']);
        }

        return $this->complete($request, $createAuthor, $pending);
    }

    /** Create the author, sign them in and clear the registration state. */
    private function complete(Request $request, CreateAuthor $createAuthor, array $details): RedirectResponse
    {
        $user = $createAuthor->handle(
            $details,
            isset($details['provider']) ? SocialProvider::from($details['provider']) : null,
            $details['provider_user_id'] ?? null,
        );

        $request->session()->forget([self::SESSION_KEY, OrcidController::SESSION_KEY]);
        Auth::login($user, remember: isset($details['provider']));
        $request->session()->regenerate();

        return redirect()->route('account.dashboard')->with('success', 'Welcome to '.settings('general.application_name').'! Your account is ready — you can submit a manuscript now.');
    }

    /** Abandon a half-finished Google sign-up and show the full registration options again. */
    public function reset(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('register');
    }

    public function resend(Request $request): RedirectResponse
    {
        $email = $request->session()->get(self::SESSION_KEY.'.email');
        abort_unless($email, 419);

        $key = 'otp-resend:'.$email;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            return back()->withErrors(['otp' => 'Please wait '.RateLimiter::availableIn($key).' seconds before requesting another code.']);
        }
        RateLimiter::hit($key, 600);

        $this->otp->issue($email);

        return back()->with('status', 'A new code has been sent.');
    }
}
