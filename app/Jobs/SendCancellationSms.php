<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Sms\SmsProviderInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendCancellationSms implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public function __construct(public Appointment $appointment)
    {
        $this->onQueue('high');
    }

    public function handle(SmsProviderInterface $sms): void
    {
        $patient = $this->appointment->patient;

        if (!$patient || !$patient->phone) {
            return;
        }

        $message = sprintf(
            'تم إلغاء موعدك في عيادة بلسم. المرجع: %s. التاريخ: %s.',
            $this->appointment->reference,
            $this->appointment->start_at->format('Y-m-d H:i')
        );

        $sms->send($patient->phone, $message);
    }
}
