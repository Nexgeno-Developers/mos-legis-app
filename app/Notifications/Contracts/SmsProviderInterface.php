<?php

namespace App\Notifications\Contracts;

interface SmsProviderInterface
{
    public function send(string $mobile, string $message, array $options = [], array $templateData = []): array;
}
