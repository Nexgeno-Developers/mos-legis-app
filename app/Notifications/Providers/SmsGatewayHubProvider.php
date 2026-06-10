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

        try {
            $response = Http::withoutVerifying()->get(
                'https://www.smsgatewayhub.com/api/mt/SendSMS',
                [
                    'APIKey' => $config['api_key'],
                    'senderid' => $config['sender_id'],
                    'channel' => 2,
                    'DCS' => 0,
                    'flashsms' => 0,
                    'number' => $mobile,
                    'text' => $message,
                    'route' => $config['route'],
                ]
            );

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
