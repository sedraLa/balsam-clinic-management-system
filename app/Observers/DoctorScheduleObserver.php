<?php

namespace App\Observers;

use App\Models\DoctorSchedule;
use Illuminate\Support\Facades\Cache;

class DoctorScheduleObserver
{
    public function saved(DoctorSchedule $schedule): void
    {
        $this->bust();
    }

    public function deleted(DoctorSchedule $schedule): void
    {
        $this->bust();
    }

    private function bust(): void
    {
        Cache::increment('doctors:list:version');
    }
}
