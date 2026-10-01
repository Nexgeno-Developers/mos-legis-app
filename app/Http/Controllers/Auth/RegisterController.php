<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\CreateAuthor;
use App\Enums\SocialProvider;
use App\Http\Controllers\Controller;
use App\Models\AuthorCategory;
use App\Models\User;
use App\Services\Auth\OtpService;
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
 * Also completes Google/ORCID sign-ups that need an email address.
 */
class RegisterController extends Controller
{
    private const SESSION_KEY = 'pending_registration';

    public function __construct(private readonly OtpService $otp) {}

    public function create(Request $request): View
    {
        return view('auth.register', [
            'authorCategories' => AuthorCategory::active()->orderBy('name')->pluck('name', 'id'),
            'social' => $request->session()->get(self::SESSION_KEY.'.provider') ? $request->session()->get(self::SESSION_KEY) : null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY, []);
        $isSocial = isset($pending['provider']);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{7,20}$/'],
            'author_category_id' => ['nullable', 'integer', Rule::exists('manuscript_author_categories', 'id')->where('status', 'Active')],
            'password' => [$isSocial ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'terms' => ['accepted'],
        ]);

        $request->session()->put(self::SESSION_KEY, array_merge($pending, [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'] ?? null,
            'author_category_id' => $data['author_category_id'] ?? null,
            'password' => ! empty($data['password']) ? Hash::make($data['password']) : null,
        ]));

        $this->otp->issue(strtolower($data['email']));

        return redirect()->route('register.verify')->with('status', "We've emailed a 6-digit code to {$data['email']}.");
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

        $user = $createAuthor->handle(
            $pending,
            isset($pending['provider']) ? SocialProvider::from($pending['provider']) : null,
            $pending['provider_user_id'] ?? null,
        );

        $request->session()->forget(self::SESSION_KEY);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.profile.edit')->with('success', 'Welcome to '.settings('general.application_name').'! Complete your author profile before submitting.');
    }

    /** Abandon a half-finished (e.g. ORCID) sign-up and show the full registration options again. */
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
