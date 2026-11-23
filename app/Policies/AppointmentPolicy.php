<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function view(User $user, Appointment $appointment): bool
    {
        return match ($user->role) {
            UserRole::Admin        => true,
            UserRole::Receptionist => $user->branch_id === $appointment->branch_id,
            UserRole::Doctor       => $user->doctor?->id === $appointment->doctor_id,
            UserRole::Patient      => $user->patient?->id === $appointment->patient_id,
        };
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return match ($user->role) {
            UserRole::Admin        => true,
            UserRole::Receptionist => $user->branch_id === $appointment->branch_id,
            UserRole::Doctor       => false,
            UserRole::Patient      => $user->patient?->id === $appointment->patient_id,
        };
    }
}
