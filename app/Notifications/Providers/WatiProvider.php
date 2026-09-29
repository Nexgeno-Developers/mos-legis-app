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

        // IMPORTANT:
        // $message must contain the actual WATI template name
        $templateName = $message;

        $watiParameters = $templateData['whatsapp_options']['wati']['parameters'] ?? [];

        $parameters = [];

        foreach ($watiParameters as $parameter) {
            $parameters[] = [
                'name' => $parameter['name'],
                'value' => $this->replaceParameters(
                    (string) ($parameter['value'] ?? ''),
                    $options
                ),
            ];
        }

        $payload = [
            'broadcast_name' => $templateName,
            'template_name' => $templateName,
            'parameters' => $parameters,
        ];

        $url = rtrim($config['api_url'], '/')
            .'/api/v1/sendTemplateMessage?whatsappNumber='.$mobile;

        try {

            $response = Http::withHeaders([
                'Authorization' => $config['api_token'], // token should already contain "Bearer "
                'Content-Type' => 'text/json',
            ])->withBody(
                json_encode($payload),
                'text/json'
            )->send('POST', $url);

            return [
                'status' => $response->successful(),
                'http_status' => $response->status(),
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

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $content
        );
    }
}
