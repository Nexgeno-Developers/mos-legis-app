<?php

namespace App\Notifications\Providers;

use App\Notifications\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class SmsGatewayHubProvider implements SmsProviderInterface
{
    public function send(string $mobile, string $message): array
    {
        $config = config('notification.providers.sms.smsgatewayhub');

        $payload = [
            'APIKey' => $config['api_key'],
            'senderid' => $config['sender_id'],
            'channel' => 2,
            'DCS' => 0,
            'flashsms' => 0,
            'number' => $mobile,
            'text' => $message,
            'route' => $config['route'],
        ];

        try {
            $response = Http::withoutVerifying()->get(
                'https://www.smsgatewayhub.com/api/mt/SendSMS',
                $payload
            );

            return [
                'status' => $response->successful(),
                'payload' => collect($payload)->except(['APIKey', 'senderid'])->toArray(),
                'response' => $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'status' => false,
                'payload' => collect($payload)->except(['APIKey', 'senderid'])->toArray(),
                'response' => $e->getMessage(),
            ];
        }
    }
}
