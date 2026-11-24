<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\MedicalRecord;
use App\Observers\AppointmentObserver;
use App\Observers\DoctorObserver;
use App\Observers\DoctorScheduleObserver;
use App\Observers\MedicalRecordObserver;
use App\Policies\AppointmentPolicy;
use App\Policies\MedicalRecordPolicy;
use App\Sms\LogSmsProvider;
use App\Sms\SmsProviderInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsProviderInterface::class, LogSmsProvider::class);
    }

    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        MedicalRecord::observe(MedicalRecordObserver::class);
        Doctor::observe(DoctorObserver::class);
        DoctorSchedule::observe(DoctorScheduleObserver::class);

        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(MedicalRecord::class, MedicalRecordPolicy::class);
    }
}
