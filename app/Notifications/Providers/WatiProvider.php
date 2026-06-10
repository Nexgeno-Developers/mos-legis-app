<?php

namespace App\Notifications\Providers;

use App\Notifications\Contracts\WhatsappProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class WatiProvider implements WhatsappProviderInterface
{
    public function send(string $mobile, string $message): array
    {
        $config = config('notification.providers.whatsapp.wati');

        try {
            $response = Http::withToken($config['api_token'])
                ->post(rtrim($config['api_url'], '/').'/api/v1/sendSessionMessage/'.$mobile, [
                    'messageText' => $message,
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
