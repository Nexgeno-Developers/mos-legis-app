<?php

namespace App\Notifications;

use App\Enums\RoleName;
use App\Jobs\SendNotificationJob;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Sends workflow notifications (SOW A.16 "notified by email on each event";
 * SOW Inclusions: email, WhatsApp and SMS) through the admin-editable
 * notification templates. Jobs are queued after the DB transaction commits.
 */
class WorkflowNotifier
{
    /** @param array<string, scalar|null> $data template placeholders */
    public function toUser(string $template, ?User $user, array $data = []): void
    {
        if (! $user || ! $user->isActive()) {
            return;
        }

        SendNotificationJob::dispatch(
            $template,
            ['email' => $user->email, 'mobile' => $user->phone],
            $data + ['recipient_name' => $user->name],
            $this->channels($template),
        )->afterCommit();
    }

    /** Every active superadmin. */
    public function toAdmins(string $template, array $data = []): void
    {
        User::role(RoleName::Superadmin->value)->active()->get()
            ->each(fn (User $admin) => $this->toUser($template, $admin, $data));
    }

    /** Plain email address (e.g. the Application Email setting). */
    public function toEmail(string $template, ?string $email, array $data = []): void
    {
        if (! $email) {
            return;
        }

        SendNotificationJob::dispatch($template, ['email' => $email], $data + ['recipient_name' => 'Editorial Office'], ['email'])
            ->afterCommit();
    }

    /**
     * SOW A.21 "Manuscript Email Notifications Enable/Disable" switches off
     * the email channel for manuscript events; other channels still follow the template.
     *
     * @return list<string>
     */
    private function channels(string $template): array
    {
        $channels = ['email', 'sms', 'whatsapp'];

        if (Str::startsWith($template, 'submission_') && ! settings()->bool('manuscript.manuscript_email_notifications_enabled')) {
            $channels = ['sms', 'whatsapp'];
        }

        return $channels;
    }
}
