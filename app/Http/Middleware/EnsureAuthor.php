<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * The author portal is for active author ("Member") accounts only (SOW B.01).
 */
class EnsureAuthor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $user->isActive()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();

            return redirect()->route('login')->withErrors(['email' => 'This account has been deactivated.']);
        }

        if (! $user->isAuthor()) {
            return $user->canAccessAdmin()
                ? redirect()->route('admin.dashboard')->with('error', 'The author portal is only available to author accounts.')
                : abort(403);
        }

        return $next($request);
    }
}
