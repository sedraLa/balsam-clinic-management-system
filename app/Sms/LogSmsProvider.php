<?php

namespace App\Sms;

use Illuminate\Support\Facades\Log;

class LogSmsProvider implements SmsProviderInterface
{
    public function send(string $to, string $message): void
    {
        Log::channel('single')->info('SMS', ['to' => $to, 'message' => $message]);
    }
}
