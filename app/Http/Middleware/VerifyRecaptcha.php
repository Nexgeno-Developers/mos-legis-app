<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VerifyRecaptcha
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->input('recaptcha_token');
        $action = $request->input('recaptcha_action');

        if (!$token || !$action) {
            Log::warning('Missing reCAPTCHA token or action.');
            abort(403, 'ReCAPTCHA verification failed.');
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('custom.recaptcha_secret_key'),
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        $data = $response->json();
        Log::info('reCAPTCHA response', $data);

        if (!$data['success'] || $data['action'] !== $action || $data['score'] < 0.5) {
            Log::warning('reCAPTCHA failed', [
                'score' => $data['score'] ?? 'N/A',
                'action' => $data['action'] ?? 'N/A',
            ]);
            abort(403, 'reCAPTCHA verification failed.');
        }

        return $next($request);
    }
}