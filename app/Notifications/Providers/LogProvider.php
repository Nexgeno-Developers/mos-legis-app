<?php

namespace App\Notifications\Providers;

use App\Notifications\Contracts\SmsProviderInterface;
use App\Notifications\Contracts\WhatsappProviderInterface;
use Illuminate\Support\Facades\Log;

/**
 * Development driver: writes SMS/WhatsApp messages to the log instead of calling a gateway.
 */
class LogProvider implements SmsProviderInterface, WhatsappProviderInterface
{
    public function send(string $mobile, string $message, array $options = [], array $templateData = []): array
    {
        Log::info('Notification (log driver)', ['to' => $mobile, 'message' => $message]);

        return ['status' => true, 'payload' => ['to' => $mobile], 'response' => 'logged'];
    }
}
