<?php

namespace App\Notifications\Providers;

use App\Notifications\Contracts\WhatsappProviderInterface;
use Illuminate\Support\Facades\Http;
use Throwable;

class WatiProvider implements WhatsappProviderInterface
{
    public function send(string $mobile, string $message, array $options = [], array $templateData = []): array
    {
        $config = config('notification.providers.whatsapp.wati');
        $templateName = $message;
        $watiParameters = $templateData['whatsapp_options']['wati']['parameters'] ?? [];
        $parameters = [];

        if (! empty($watiParameters)) {
            foreach ($watiParameters as $parameter) {
                $parameters[] = [
                    'name' => $parameter['name'],
                    'value' => $this->replaceParameters((string) ($parameter['value'] ?? ''), $options),
                ];
            }
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

    protected function replaceParameters(string $content, array $data): string
    {
        $replacements = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $replacements['{'.$key.'}'] = (string) $value;
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}
