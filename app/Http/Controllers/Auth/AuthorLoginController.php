<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * SOW B.01 — author (member) sign-in. Only author accounts may sign in on the website.
 */
class AuthorLoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate(fn (User $user): ?string => match (true) {
            ! $user->isActive() => 'This account has been deactivated.',
            ! $user->isAuthor() => 'Staff accounts sign in from the admin console.',
            default => null,
        });

        $request->session()->regenerate();

        return redirect()->intended(route('account.dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
