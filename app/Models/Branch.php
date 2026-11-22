<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = ['name', 'code', 'address', 'phone'];

    public function doctorSchedules(): HasMany
    {
        return $this->hasMany(DoctorSchedule::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function appointmentSequences(): HasMany
    {
        return $this->hasMany(AppointmentSequence::class);
    }
}
