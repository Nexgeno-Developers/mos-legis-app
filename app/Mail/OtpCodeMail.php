<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email OTP for author registration (SOW B.01). Sent immediately (not queued) so the code arrives while the user waits.
 */
class OtpCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code, public int $minutes) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->code.' is your '.settings('general.application_name').' verification code');
    }

    public function content(): Content
    {
        return new Content(markdown: 'emails.otp-code');
    }
}
