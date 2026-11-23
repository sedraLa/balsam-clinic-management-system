<?php

namespace App\Sms;

interface SmsProviderInterface
{
    public function send(string $to, string $message): void;
}
