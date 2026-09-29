<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    /**
     * @param  array{email?: string|null, mobile?: string|null}  $recipient
     * @param  list<string>  $channels
     */
    public function __construct(
        public string $slug,
        public array $recipient,
        public array $data,
        public array $channels = ['email', 'sms', 'whatsapp'],
    ) {}

    public function handle(NotificationService $service): void
    {
        $service->sendTemplate($this->slug, $this->recipient, $this->data, $this->channels);
    }
}
