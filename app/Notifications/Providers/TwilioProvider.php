<?php

namespace App\Notifications\Providers;

use App\Notifications\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class TwilioProvider implements SmsProviderInterface
{
    public function send(string $mobile, string $message): array
    {
        $config = config('notification.providers.sms.twilio');

        try {
            $response = Http::withBasicAuth(
                $config['account_sid'],
                $config['auth_token']
            )->asForm()->post(
                "https://api.twilio.com/2010-04-01/Accounts/{$config['account_sid']}/Messages.json",
                [
                    'From' => $config['from'],
                    'To' => $mobile,
                    'Body' => $message,
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
