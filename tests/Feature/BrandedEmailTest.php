<?php

namespace Tests\Feature;

use App\Mail\OtpCodeMail;
use App\Models\NotificationTemplate;
use App\Notifications\Contracts\EmailProviderInterface;
use App\Notifications\Providers\MailProvider;
use App\Services\NotificationService;
use App\Support\BrandedEmail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Every email goes out in the branded MOS Legis frame.
 */
class BrandedEmailTest extends TestCase
{
    #[Test]
    public function the_frame_carries_the_brand_subject_and_body(): void
    {
        $html = BrandedEmail::render('Your job posting is live', '<p>Dear Ananya,</p><p>Approved.</p>', ['action' => ['label' => 'View job', 'url' => 'https://example.com/jobs']]);

        $this->assertStringContainsString('Rooted in Tradition', $html);
        $this->assertStringContainsString('#b01b25', $html);                      // brand crimson
        $this->assertStringContainsString('Your job posting is live', $html);
        $this->assertStringContainsString('<p>Dear Ananya,</p><p>Approved.</p>', $html);
        $this->assertStringContainsString('href="https://example.com/jobs"', $html);
        $this->assertStringContainsString('Dear Ananya, Approved.', $html);        // inbox preview text
    }

    #[Test]
    public function notification_template_emails_are_sent_in_the_frame(): void
    {
        NotificationTemplate::create([
            'slug' => 'test_notice', 'name' => 'Test', 'email_subject' => 'Hello {name}', 'email_template' => '<p>Body for {name}</p>',
            'email_enabled' => true, 'sms_enabled' => false, 'whatsapp_enabled' => false, 'status' => true,
        ]);
        config(['notification.email_provider' => 'mail']);

        $sent = null;
        $this->app->instance(MailProvider::class, new class($sent) implements EmailProviderInterface
        {
            public function __construct(public ?array &$sent) {}

            public function send(string $email, string $subject, string $body): array
            {
                $this->sent = compact('email', 'subject', 'body');

                return ['status' => true, 'payload' => [], 'response' => 'ok'];
            }
        });

        app(NotificationService::class)->sendTemplate('test_notice', ['email' => 'a@example.com'], ['name' => 'Asha'], ['email']);

        $this->assertSame('Hello Asha', $sent['subject']);
        $this->assertStringContainsString('<p>Body for Asha</p>', $sent['body']);
        $this->assertStringContainsString('Rooted in Tradition', $sent['body']);
    }

    #[Test]
    public function the_otp_email_uses_the_same_frame(): void
    {
        $html = (new OtpCodeMail('482913', 10))->render();

        $this->assertStringContainsString('482913', $html);
        $this->assertStringContainsString('Verify your email', $html);
        $this->assertStringContainsString('Rooted in Tradition', $html);
    }
}
