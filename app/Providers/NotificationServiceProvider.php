<?php

namespace App\Providers;

use App\Notifications\Contracts\EmailProviderInterface;
use App\Notifications\Contracts\SmsProviderInterface;
use App\Notifications\Contracts\WhatsappProviderInterface;
use App\Notifications\Providers\BrevoProvider;
use App\Notifications\Providers\GmailProvider;
use App\Notifications\Providers\SmsGatewayHubProvider;
use App\Notifications\Providers\TwilioProvider;
use App\Notifications\Providers\WatiProvider;
use App\Services\NotificationService;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(NotificationService::class);

        $this->app->bind(SmsProviderInterface::class, function () {
            return match (config('notification.sms_provider')) {
                'twilio' => $this->app->make(TwilioProvider::class),
                default => $this->app->make(SmsGatewayHubProvider::class),
            };
        });

        $this->app->bind(WhatsappProviderInterface::class, function () {
            return match (config('notification.whatsapp_provider')) {
                'wati' => $this->app->make(WatiProvider::class),
                default => $this->app->make(WatiProvider::class),
            };
        });

        $this->app->bind(EmailProviderInterface::class, function () {
            return match (config('notification.email_provider')) {
                'brevo' => $this->app->make(BrevoProvider::class),
                default => $this->app->make(GmailProvider::class),
            };
        });
    }
}
