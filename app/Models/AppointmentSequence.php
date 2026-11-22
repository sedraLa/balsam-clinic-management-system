<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentSequence extends Model
{
    protected $fillable = ['branch_id', 'year', 'last_number'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
