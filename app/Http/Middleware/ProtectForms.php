<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class ProtectForms
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->header('User-Agent');
        $ip = $request->ip();

        // 🧱 1. Block empty User-Agent
        if (empty($userAgent)) {
            \Log::warning("Empty User-Agent blocked from: $ip");
            abort(403, 'Forbidden - Missing User Agent');
        }

        // 🚫 2. Block known bad bots
        $badAgents = ['curl', 'wget', 'python', 'bot', 'scrapy', 'PostmanRuntime'];
        foreach ($badAgents as $bot) {
            if (stripos($userAgent, $bot) !== false) {
                \Log::warning("Bot User-Agent '$userAgent' blocked from: $ip");
                abort(403, 'Forbidden - Bot Detected');
            }
        }

        //🧩 3. Optional: Block non-browser requests (optional)
        // if (!$request->ajax() && !$request->expectsJson() && !$request->isMethod('post')) {
        //     \Log::warning("Suspicious form access attempt from: $ip");
        //     abort(403, 'Forbidden - Suspicious Request');
        // }

        // // ✅ 4. Filter for Suspicious Patterns Inputs
        // $suspiciousPatterns = [
        //     '/<script\b[^>]*>(.*?)<\/script>/is',  // Full <script> tags
        //     '/javascript:/i',                      // "javascript:" in links
        //     '/on\w+\s*=\s*["\'].*?["\']/i',        // Inline event handlers like onclick=""
        //     '/<iframe\b[^>]*>(.*?)<\/iframe>/is',  // Malicious iframes
        //     '/<img\b[^>]*onerror\s*=\s*["\'].*?["\']/i', // Image-based XSS
        // ];

        // foreach ($request->all() as $key => $value) {
        //     if (!is_string($value)) continue;

        //     foreach ($suspiciousPatterns as $pattern) {
        //         if (preg_match($pattern, $value)) {
        //             Log::warning("Suspicious input blocked on '$key' with value '$value' from $ip");
        //             abort(403, 'Forbidden - Suspicious Input Detected');
        //         }
        //     }
        // }
        
        /*
        |--------------------------------------------------------------------------
        | 4. BASIC INJECTION DETECTION (SAFE VERSION)
        |--------------------------------------------------------------------------
        | NOTE: We avoid fragile regex on HTML, instead check encoded patterns
        */
        $payload = json_encode($request->all());

        $dangerPatterns = [
            '<script', '</script',
            'javascript:',
            'onerror=',
            'onclick=',
            'onload=',
            '<iframe',
            'data:text/html'
        ];

        foreach ($dangerPatterns as $pattern) {
            if (stripos($payload, $pattern) !== false) {
                Log::warning("Suspicious payload blocked from {$ip}");
                abort(403);
            }
        }
        

        // ✅ Optional: If you're using reCAPTCHA v3, you can validate here
        // (Let me know if you want that too)

        // 🕳️ 6. Honeypot check (disabled - conflicts with legitimate field)
        // if ($request->filled('website')) { // "website" is our honeypot field
        //     Log::warning("Honeypot field triggered by spam bot from IP: $ip");
        //     abort(403, 'Forbidden - Bot detected');
        // }

        return $next($request);
    }
}
