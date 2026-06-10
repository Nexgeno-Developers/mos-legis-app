<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $slug,
        public array $recipient,
        public array $data
    ) {}

    public function handle(NotificationService $service): void
    {
        $service->sendTemplate($this->slug, $this->recipient, $this->data);
    }
}
