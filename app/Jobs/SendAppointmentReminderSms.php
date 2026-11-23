<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Sms\SmsProviderInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAppointmentReminderSms implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
        $this->onQueue('notifications');
    }

    public function handle(SmsProviderInterface $sms): void
    {
        $patient = $this->appointment->patient;

        if (!$patient || !$patient->phone) {
            return;
        }

        $message = sprintf(
            'تذكير: لديك موعد غداً في عيادة بلسم. المرجع: %s. الوقت: %s.',
            $this->appointment->reference,
            $this->appointment->start_at->format('H:i')
        );

        $sms->send($patient->phone, $message);
    }
}
