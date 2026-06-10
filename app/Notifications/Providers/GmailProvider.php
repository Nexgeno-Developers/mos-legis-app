<?php

namespace App\Notifications\Providers;

use App\Notifications\Contracts\EmailProviderInterface;
use Illuminate\Support\Facades\Mail;
use Throwable;

class GmailProvider implements EmailProviderInterface
{
    public function send(string $email, string $subject, string $body): array
    {
        $config = config('notification.providers.email.gmail');

        try {
            Mail::html($body, function ($mail) use ($email, $subject, $config) {
                $mail->to($email)
                    ->subject($subject)
                    ->from($config['from_email'], $config['from_name']);
            });

            return [
                'status' => true,
                'response' => 'sent via mail transport',
            ];
        } catch (Throwable $e) {
            return [
                'status' => false,
                'response' => $e->getMessage(),
            ];
        }
    }
}
