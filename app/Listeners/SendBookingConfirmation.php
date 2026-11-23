<?php

namespace App\Listeners;

use App\Events\AppointmentBooked;
use App\Jobs\SendBookingConfirmationSms;

class SendBookingConfirmation
{
    public function handle(AppointmentBooked $event): void
    {
        SendBookingConfirmationSms::dispatch($event->appointment);
    }
}
