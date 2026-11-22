<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'phone',
        'date_of_birth',
        'gender',
        'booking_banned_until',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'booking_banned_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }
}
