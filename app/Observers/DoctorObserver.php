<?php

namespace App\Observers;

use App\Models\Doctor;
use Illuminate\Support\Facades\Cache;

class DoctorObserver
{
    public function saved(Doctor $doctor): void
    {
        $this->bust();
    }

    public function deleted(Doctor $doctor): void
    {
        $this->bust();
    }

    private function bust(): void
    {
        Cache::increment('doctors:list:version');
    }
}
