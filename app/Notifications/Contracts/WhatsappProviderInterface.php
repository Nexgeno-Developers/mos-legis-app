<?php

namespace App\Notifications\Contracts;

interface WhatsappProviderInterface
{
    public function send(string $mobile, string $message, array $options = []): array;
}
