<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    public function view(User $user, MedicalRecord $record): bool
    {
        $appointment = $record->appointment;

        return match ($user->role) {
            UserRole::Admin        => true,
            UserRole::Receptionist => $user->branch_id === $appointment->branch_id,
            UserRole::Doctor       => $user->doctor?->id === $appointment->doctor_id,
            UserRole::Patient      => $user->patient?->id === $appointment->patient_id,
        };
    }

    public function create(User $user, MedicalRecord $record): bool
    {
        $appointment = $record->appointment;

        return match ($user->role) {
            UserRole::Admin  => true,
            UserRole::Doctor => $user->doctor?->id === $appointment->doctor_id,
            default          => false,
        };
    }

    public function update(User $user, MedicalRecord $record): bool
    {
        return $this->create($user, $record);
    }
}
