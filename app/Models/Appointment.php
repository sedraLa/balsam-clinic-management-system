<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'reference',
        'doctor_id',
        'patient_id',
        'branch_id',
        'start_at',
        'end_at',
        'status',
        'reminder_sent_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status'           => AppointmentStatus::class,
            'start_at'         => 'datetime',
            'end_at'           => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function scopeForBranch(Builder $query, int $branchId): Builder
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeForDoctor(Builder $query, int $doctorId): Builder
    {
        return $query->where('doctor_id', $doctorId);
    }

    public function scopeForPatient(Builder $query, int $patientId): Builder
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeWithStatus(Builder $query, AppointmentStatus $status): Builder
    {
        return $query->where('status', $status->value);
    }

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('start_at', $date);
    }

    public function scopeForSpecialty(Builder $query, int $specialtyId): Builder
    {
        return $query->whereHas('doctor', fn($q) => $q->where('specialty_id', $specialtyId));
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }
}
