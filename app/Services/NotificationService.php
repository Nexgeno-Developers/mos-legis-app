<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\NotificationTemplate;
use App\Notifications\Contracts\EmailProviderInterface;
use App\Notifications\Contracts\SmsProviderInterface;
use App\Notifications\Contracts\WhatsappProviderInterface;
use App\Notifications\Providers\BrevoProvider;
use App\Notifications\Providers\LogProvider;
use App\Notifications\Providers\MailProvider;
use App\Notifications\Providers\SmsGatewayHubProvider;
use App\Notifications\Providers\TwilioProvider;
use App\Notifications\Providers\WatiProvider;
use App\Support\BrandedEmail;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class NotificationService
{
    /** @param list<string> $channels */
    public function sendTemplate(string $slug, array $recipient, array $data, array $channels = ['email', 'sms', 'whatsapp']): void
    {
        $template = NotificationTemplate::query()
            ->where('slug', $slug)
            ->where('status', true)
            ->first();

        if (! $template) {
            Log::warning('NotificationService: template not found or inactive', ['slug' => $slug]);

            return;
        }

        if (in_array('sms', $channels, true) && $template->sms_enabled && ! empty($recipient['mobile']) && $template->sms_template) {
            $this->sendSms(
                $slug,
                $recipient['mobile'],
                $this->replaceVariables($template->sms_template, $data),
                $data,
                $template->toArray()
            );
        }

        if (in_array('whatsapp', $channels, true) && $template->whatsapp_enabled && ! empty($recipient['mobile']) && $template->whatsapp_template) {
            $this->sendWhatsapp(
                $slug,
                $recipient['mobile'],
                $this->replaceVariables($template->whatsapp_template, $data),
                $data,
                $template->toArray()
            );
        }

        if (in_array('email', $channels, true) && $template->email_enabled && ! empty($recipient['email']) && $template->email_template) {
            $subject = $this->replaceVariables($template->email_subject ?? '', $data);

            $this->sendEmail(
                $slug,
                $recipient['email'],
                $subject,
                $this->replaceVariables($template->email_template, $data, escape: true)
            );
        }
    }

    protected function replaceVariables(string $content, array $data, bool $escape = false): string
    {
        $replacements = [];

        foreach ($data as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $replacements['{'.$key.'}'] = $escape ? e((string) $value) : (string) $value;
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    protected function sendSms(string $templateSlug, string $mobile, string $message, array $options = [], array $templateData = []): void
    {
        $providerName = config('notification.sms_provider');
        $provider = $this->resolveSmsProvider($providerName);
        $result = $provider->send($mobile, $message, $options, $templateData);

        $this->logNotification($templateSlug, 'sms', $providerName, $mobile, $message, $result);
    }

    protected function sendWhatsapp(string $templateSlug, string $mobile, string $message, array $options = [], array $templateData = []): void
    {
        $providerName = config('notification.whatsapp_provider');
        $provider = $this->resolveWhatsappProvider($providerName);
        $result = $provider->send($mobile, $message, $options, $templateData);

        $this->logNotification($templateSlug, 'whatsapp', $providerName, $mobile, $message, $result);
    }

    protected function sendEmail(string $templateSlug, string $email, string $subject, string $body): void
    {
        $providerName = config('notification.email_provider');
        $provider = $this->resolveEmailProvider($providerName);
        // Every email goes out in the branded MOS Legis frame; the log keeps the template body.
        $result = $provider->send($email, $subject, BrandedEmail::render($subject, $body));

        $this->logNotification($templateSlug, 'email', $providerName, $email, $body, $result, $subject);
    }

    protected function resolveSmsProvider(string $providerName): SmsProviderInterface
    {
        return match ($providerName) {
            'twilio' => app(TwilioProvider::class),
            'log' => app(LogProvider::class),
            'smsgatewayhub' => app(SmsGatewayHubProvider::class),
            default => throw new InvalidArgumentException("Unsupported SMS provider [{$providerName}]"),
        };
    }

    protected function resolveWhatsappProvider(string $providerName): WhatsappProviderInterface
    {
        return match ($providerName) {
            'wati' => app(WatiProvider::class),
            'log' => app(LogProvider::class),
            default => throw new InvalidArgumentException("Unsupported WhatsApp provider [{$providerName}]"),
        };
    }

    protected function resolveEmailProvider(string $providerName): EmailProviderInterface
    {
        return match ($providerName) {
            'brevo' => app(BrevoProvider::class),
            'mail' => app(MailProvider::class),
            default => throw new InvalidArgumentException("Unsupported email provider [{$providerName}]"),
        };
    }

    protected function logNotification(
        string $templateSlug,
        string $channel,
        string $provider,
        string $recipient,
        string $message,
        array $result,
        ?string $subject = null
    ): void {
        $logMessage = $subject
            ? "Subject: {$subject}\n\n{$message}"
            : $message;

        NotificationLog::create([
            'template_slug' => $templateSlug,
            'channel' => $channel,
            'provider' => $provider,
            'recipient' => $recipient,
            'message' => $logMessage,
            'status' => (bool) ($result['status'] ?? false),
            'response' => $result['response'] ?? null,
            'payload' => $result['payload'] ?? null,
        ]);
    }
}
