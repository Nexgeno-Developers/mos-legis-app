<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Only active Superadmin/Reviewer accounts may use the admin panel (SOW A.10:
 * Authors work from the website only). Deactivated users are signed out.
 */
class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('admin.login'));
        }

        if (! $user->isActive() || ! $user->canAccessAdmin()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->withErrors([
                'email' => $user->isActive()
                    ? 'This account cannot access the admin panel.'
                    : 'This account has been deactivated.',
            ]);
        }

        return $next($request);
    }
}
