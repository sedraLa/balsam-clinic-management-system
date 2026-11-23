<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Enums\WeekDay;
use App\Http\Requests\AvailableSlotsRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AvailableSlotsController extends Controller
{
    public function __invoke(AvailableSlotsRequest $request, Doctor $doctor)
    {
        $branchId = (int) $request->branch_id;
        $date     = $request->date;

        $cacheKey = "available_appointments:{$doctor->id}:{$branchId}:{$date}";

        $slots = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($doctor, $branchId, $date) {
            return $this->computeSlots($doctor, $branchId, $date);
        });

        return response()->json(['data' => $slots]);
    }

    private function computeSlots(Doctor $doctor, int $branchId, string $date): array
    {
        $dayName  = strtolower(Carbon::parse($date)->format('l'));
        $weekDay  = WeekDay::from($dayName);

        $schedules = DoctorSchedule::where('doctor_id', $doctor->id)
            ->where('branch_id', $branchId)
            ->where('day_of_week', $weekDay->value)
            ->where('is_active', true)
            ->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        $bookedRanges = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('start_at', $date)
            ->whereNotIn('status', [
                AppointmentStatus::Cancelled->value,
                AppointmentStatus::NoShow->value,
            ])
            ->get(['start_at', 'end_at'])
            ->map(fn($a) => [
                'start' => Carbon::parse($a->start_at),
                'end'   => Carbon::parse($a->end_at),
            ]);

        $now   = now();
        $slots = [];

        foreach ($schedules as $schedule) {
            $cursor = Carbon::parse($date . ' ' . $schedule->start_time);
            $end    = Carbon::parse($date . ' ' . $schedule->end_time);

            while ($cursor->copy()->addMinutes($schedule->slot_duration)->lte($end)) {
                $slotStart = $cursor->copy();
                $slotEnd   = $cursor->copy()->addMinutes($schedule->slot_duration);

                if ($slotStart->gt($now)) {
                    $occupied = $bookedRanges->first(
                        fn($r) => $slotStart->lt($r['end']) && $slotEnd->gt($r['start'])
                    );

                    if (!$occupied) {
                        $slots[] = [
                            'start_at'      => $slotStart->format('Y-m-d H:i:s'),
                            'end_at'        => $slotEnd->format('Y-m-d H:i:s'),
                            'slot_duration' => $schedule->slot_duration,
                        ];
                    }
                }

                $cursor->addMinutes($schedule->slot_duration);
            }
        }

        return $slots;
    }
}
