<?php

namespace App\Services;

use App\Enums\AppointmentStatus;
use App\Enums\UserRole;
use App\Enums\WeekDay;
use App\Events\AppointmentBooked;
use App\Events\AppointmentCancelled;
use App\Events\AppointmentMarkedAbsent;
use App\Exceptions\BookingException;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AppointmentBookingService
{
    public function book(array $data, User $bookedBy): Appointment
    {
        $doctor    = Doctor::findOrFail($data['doctor_id']);
        $patient   = Patient::findOrFail($data['patient_id']);
        $startAt   = Carbon::parse($data['start_at']);
        $branchId  = (int) $data['branch_id'];

        $this->authorizeBooker($bookedBy, $branchId, $patient);

        if ($patient->booking_banned_until && $patient->booking_banned_until->isFuture()) {
            throw new BookingException(
                'المريض ممنوع من الحجز حتى ' . $patient->booking_banned_until->format('Y-m-d')
            );
        }

        if ($startAt->isPast()) {
            throw new BookingException('لا يمكن حجز موعد في وقت مضى.');
        }

        $appointment = DB::transaction(function () use ($doctor, $patient, $startAt, $branchId, $bookedBy) {
            $dayName  = strtolower($startAt->format('l'));
            $weekDay  = WeekDay::from($dayName);

            $schedule = DoctorSchedule::where('doctor_id', $doctor->id)
                ->where('branch_id', $branchId)
                ->where('day_of_week', $weekDay->value)
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();

            if (!$schedule) {
                throw new BookingException('الطبيب لا يعمل في هذا الفرع في هذا اليوم.');
            }

            $scheduleStart = Carbon::parse($startAt->format('Y-m-d') . ' ' . $schedule->start_time);
            $scheduleEnd   = Carbon::parse($startAt->format('Y-m-d') . ' ' . $schedule->end_time);

            if ($startAt->lt($scheduleStart) || $startAt->gte($scheduleEnd)) {
                throw new BookingException('الوقت المطلوب خارج ساعات عمل الطبيب.');
            }

            $diffMinutes = $scheduleStart->diffInMinutes($startAt);
            if ($diffMinutes % $schedule->slot_duration !== 0) {
                throw new BookingException('وقت البداية لا يتوافق مع فترات المواعيد المتاحة.');
            }

            $endAt = $startAt->copy()->addMinutes($schedule->slot_duration);

            if ($endAt->gt($scheduleEnd)) {
                throw new BookingException('الوقت المطلوب خارج ساعات عمل الطبيب.');
            }

            $overlap = Appointment::where('doctor_id', $doctor->id)
                ->whereNotIn('status', [
                    AppointmentStatus::Cancelled->value,
                    AppointmentStatus::NoShow->value,
                ])
                ->where(function ($q) use ($startAt, $endAt) {
                    $q->where('start_at', '<', $endAt)
                      ->where('end_at', '>', $startAt);
                })
                ->exists();

            if ($overlap) {
                throw new BookingException('هذا الموعد محجوز مسبقاً.');
            }

            return Appointment::create([
                'doctor_id'  => $doctor->id,
                'patient_id' => $patient->id,
                'branch_id'  => $branchId,
                'start_at'   => $startAt,
                'end_at'     => $endAt,
                'status'     => AppointmentStatus::Booked,
                'created_by' => $bookedBy->id,
            ]);
        });

        AppointmentBooked::dispatch($appointment);

        return $appointment;
    }

    public function cancel(Appointment $appointment, User $cancelledBy): Appointment
    {
        $nonCancellable = [
            AppointmentStatus::Completed,
            AppointmentStatus::Cancelled,
            AppointmentStatus::NoShow,
        ];

        if (in_array($appointment->status, $nonCancellable)) {
            throw new BookingException('لا يمكن إلغاء هذا الموعد.', 422);
        }

        $appointment->update(['status' => AppointmentStatus::Cancelled]);

        $appointment = $appointment->fresh();

        AppointmentCancelled::dispatch($appointment);

        return $appointment;
    }

    public function markNoShow(Appointment $appointment): Appointment
    {
        if ($appointment->status !== AppointmentStatus::Booked) {
            throw new BookingException('لا يمكن تعيين هذا الموعد كغائب.', 422);
        }

        $appointment->update(['status' => AppointmentStatus::NoShow]);

        $appointment = $appointment->fresh();

        AppointmentMarkedAbsent::dispatch($appointment);

        return $appointment;
    }

    private function authorizeBooker(User $user, int $branchId, Patient $patient): void
    {
        if ($user->role === UserRole::Admin) {
            return;
        }

        if ($user->role === UserRole::Receptionist) {
            if ($user->branch_id !== $branchId) {
                throw new BookingException('أنت غير مخول بالحجز في هذا الفرع.', 403);
            }
            return;
        }

        if ($user->role === UserRole::Patient) {
            if (!$user->patient || $user->patient->id !== $patient->id) {
                throw new BookingException('لا يمكنك الحجز لمريض آخر.', 403);
            }
            return;
        }

        throw new BookingException('غير مخول.', 403);
    }
}
