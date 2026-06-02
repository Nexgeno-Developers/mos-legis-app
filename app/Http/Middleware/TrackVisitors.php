<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visitor;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Str;

class TrackVisitors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldSkipTracking($request)) {
            return $next($request);
        }

        //$cacheKey = 'visitor_' . md5($request->ip() . '_' . $request->userAgent() . '_' . $request->path());
        $cacheKey = 'visitor_' . md5($request->ip() . '_' . $request->userAgent());

        if (!Cache::has($cacheKey)) {
            $this->trackVisitor($request);

            // Store visit cache key
            Cache::put($cacheKey, true, now()->addMinutes(config('custom.cache_minutes')));
        }

        return $next($request);
    }

    // protected function shouldSkipTracking(Request $request): bool
    // {
    //     // Skip tracking for specific routes (e.g., admin routes, assets)
    //     $skipRoutes = [
    //         'backend.*',
    //         'admin.*',
    //         'horizon.*',
    //         'telescope.*',
    //         '*.css',
    //         '*.js',
    //         '*.ico',
    //         '*.png',
    //         '*.jpg',
    //         '*.jpeg',
    //         '*.gif',
    //         '*.svg',
    //     ];

    //     foreach ($skipRoutes as $route) {
    //         if ($request->is($route)) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    protected function shouldSkipTracking(Request $request): bool
    {
        $skipPatterns = [
            'backend/',
            'admin/',
            'horizon/',
            'telescope/',
            '.css', '.js', '.ico', '.png', '.jpg', '.jpeg', '.gif', '.svg',
        ];

        return \Str::contains($request->path(), $skipPatterns);
    }    

    protected function trackVisitor(Request $request): void
    {
        $agent = new Agent();
        
        Visitor::create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'referrer' => $request->header('referer'),
            'device_type' => $this->getDeviceType($agent),
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'company_id' => config('custom.company_id'),
        ]);
    }

    protected function getDeviceType(Agent $agent): string
    {
        if ($agent->isDesktop()) {
            return 'desktop';
        } elseif ($agent->isTablet()) {
            return 'tablet';
        } elseif ($agent->isMobile()) {
            return 'mobile';
        } elseif ($agent->isRobot()) {
            return 'bot';
        }

        return 'unknown';
    }
}
