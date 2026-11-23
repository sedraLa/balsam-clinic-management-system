<?php

namespace App\Listeners;

use App\Events\AppointmentCancelled;
use App\Jobs\SendCancellationSms;

class SendCancellationConfirmation
{
    public function handle(AppointmentCancelled $event): void
    {
        SendCancellationSms::dispatch($event->appointment);
    }
}
