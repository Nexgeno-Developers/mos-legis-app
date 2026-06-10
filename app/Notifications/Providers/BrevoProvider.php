<?php

namespace App\Notifications\Providers;

use App\Notifications\Contracts\EmailProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class BrevoProvider implements EmailProviderInterface
{
    public function send(string $email, string $subject, string $body): array
    {
        $config = config('notification.providers.email.brevo');

        try {
            $response = Http::withHeaders([
                'api-key' => $config['api_key'],
                'accept' => 'application/json',
                'content-type' => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'email' => $config['from_email'],
                    'name' => $config['from_name'],
                ],
                'to' => [
                    ['email' => $email],
                ],
                'subject' => $subject,
                'htmlContent' => $body,
            ]);

            return [
                'status' => $response->successful(),
                'response' => $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'status' => false,
                'response' => $e->getMessage(),
            ];
        }
    }
}
