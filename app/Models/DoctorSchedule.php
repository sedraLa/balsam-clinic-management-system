<?php

namespace App\Models;

use App\Enums\WeekDay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_id',
        'branch_id',
        'day_of_week',
        'start_time',
        'end_time',
        'slot_duration',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => WeekDay::class,
            'is_active' => 'boolean',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
