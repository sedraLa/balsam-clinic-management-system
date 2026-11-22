<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\AppointmentSequence;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AppointmentObserver
{
    public function creating(Appointment $appointment): void
    {
        $appointment->reference = $this->generateReference($appointment->branch_id);
    }

    public function created(Appointment $appointment): void
    {
        $this->log('created', $appointment, null, $appointment->getAttributes());
        $this->invalidateCache($appointment);
    }

    public function updated(Appointment $appointment): void
    {
        $changed = $appointment->getChanges();
        unset($changed['updated_at']);

        if (empty($changed)) {
            return;
        }

        $old = array_intersect_key($appointment->getOriginal(), $changed);

        $this->log('updated', $appointment, $old, $changed);

        $availabilityFields = ['doctor_id', 'branch_id', 'start_at', 'end_at', 'status'];

        if (array_intersect_key($changed, array_flip($availabilityFields))) {
            // Invalidate old slot if doctor/branch/date changed
            $originalDoctor = $appointment->getOriginal('doctor_id') ?? $appointment->doctor_id;
            $originalBranch = $appointment->getOriginal('branch_id') ?? $appointment->branch_id;
            $originalDate   = $appointment->getOriginal('start_at') ?? $appointment->start_at;

            if (is_string($originalDate)) {
                $originalDate = substr($originalDate, 0, 10);
            } else {
                $originalDate = date('Y-m-d', strtotime($originalDate));
            }

            Cache::forget("available_appointments:{$originalDoctor}:{$originalBranch}:{$originalDate}");

            // Invalidate new slot
            $this->invalidateCache($appointment);
        }
    }

    public function deleted(Appointment $appointment): void
    {
        $this->log('deleted', $appointment, $appointment->getOriginal(), null);
        $this->invalidateCache($appointment);
    }

    private function generateReference(int $branchId): string
    {
        return DB::transaction(function () use ($branchId) {
            $year = now()->year;

            $sequence = AppointmentSequence::where('branch_id', $branchId)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($sequence) {
                $sequence->increment('last_number');
                $sequence->refresh();
                $number = $sequence->last_number;
            } else {
                try {
                    $sequence = AppointmentSequence::create([
                        'branch_id'   => $branchId,
                        'year'        => $year,
                        'last_number' => 1,
                    ]);
                    $number = 1;
                } catch (\Illuminate\Database\QueryException $e) {
                    // Another process inserted first; lock and increment
                    $sequence = AppointmentSequence::where('branch_id', $branchId)
                        ->where('year', $year)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $sequence->increment('last_number');
                    $sequence->refresh();
                    $number = $sequence->last_number;
                }
            }

            $branch = \App\Models\Branch::find($branchId);

            return sprintf('%s-%d-%06d', $branch->code, $year, $number);
        });
    }

    private function invalidateCache(Appointment $appointment): void
    {
        $date = $appointment->start_at instanceof \Carbon\Carbon
            ? $appointment->start_at->format('Y-m-d')
            : substr((string) $appointment->start_at, 0, 10);

        Cache::forget("available_appointments:{$appointment->doctor_id}:{$appointment->branch_id}:{$date}");
    }

    private function log(string $event, Appointment $appointment, ?array $old, ?array $new): void
    {
        $user = auth()->user();

        ActivityLog::create([
            'subject_type' => Appointment::class,
            'subject_id'   => $appointment->id,
            'causer_type'  => $user ? get_class($user) : null,
            'causer_id'    => $user?->id,
            'event'        => $event,
            'old_values'   => $old ?: null,
            'new_values'   => $new ?: null,
        ]);
    }
}
