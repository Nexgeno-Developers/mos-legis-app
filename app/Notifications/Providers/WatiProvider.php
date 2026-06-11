<?php

namespace App\Notifications\Providers;

use App\Notifications\Contracts\WhatsappProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class WatiProvider implements WhatsappProviderInterface
{
    public function send(string $mobile, string $message, array $options = []): array
    {
        $config = config('notification.providers.whatsapp.wati');
        $templateName = $message;
        $watiOptions = $options ?? [];
        $parameters = [];

        foreach ($watiOptions as $key => $value) {
            $parameters[] = [
                'name' => $key,
                'value' => (string) $value,
            ];
        }        

        $payload = [
            'broadcast_name' => $templateName,
            'template_name' => $templateName,
            'parameters' => $parameters,
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$config['api_token'],
                'Content-Type' => 'application/json',
            ])->post(
                rtrim($config['api_url'], '/')
                .'/api/v1/sendTemplateMessage?whatsappNumber='.$mobile,
                $payload
            );

            return [
                'status' => $response->successful(),
                'payload' => $payload,
                'response' => $response->body(),
            ];
        } catch (Throwable $e) {
            return [
                'status' => false,
                'payload' => $payload,
                'response' => $e->getMessage(),
            ];
        }
    }
}
