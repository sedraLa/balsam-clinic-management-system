<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Observers\AppointmentObserver;
use App\Observers\MedicalRecordObserver;
use App\Policies\AppointmentPolicy;
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

        Gate::policy(Appointment::class, AppointmentPolicy::class);
    }
}
