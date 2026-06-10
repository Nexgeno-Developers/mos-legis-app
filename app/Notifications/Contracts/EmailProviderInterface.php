<?php

namespace App\Notifications\Contracts;

interface EmailProviderInterface
{
    public function send(string $email, string $subject, string $body): array;
}
