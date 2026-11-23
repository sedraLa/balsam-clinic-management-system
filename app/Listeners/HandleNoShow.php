<?php

namespace App\Listeners;

use App\Enums\AppointmentStatus;
use App\Events\AppointmentMarkedAbsent;
use App\Models\Appointment;

class HandleNoShow
{
    public function handle(AppointmentMarkedAbsent $event): void
    {
        $patient = $event->appointment->patient;

        if (!$patient) {
            return;
        }

        $noShowCount = Appointment::where('patient_id', $patient->id)
            ->where('status', AppointmentStatus::NoShow->value)
            ->where('start_at', '>=', now()->subDays(30))
            ->count();

        if ($noShowCount >= 3) {
            $banUntil = now()->addDays(30);

            if (!$patient->booking_banned_until || $patient->booking_banned_until->lt($banUntil)) {
                $patient->update(['booking_banned_until' => $banUntil]);
            }
        }
    }
}
