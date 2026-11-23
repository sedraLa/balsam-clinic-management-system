<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Send appointment reminders every day at 08:00 Damascus time.
// withoutOverlapping() prevents a second instance starting if the first is still running.
// onOneServer() is declared for when a shared atomic cache store (Redis) is configured.
Schedule::command('appointments:send-reminders')
    ->dailyAt('08:00')
    ->timezone('Asia/Damascus')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/scheduled.log'))
    ->onFailure(function () {
        Log::channel('scheduled')->error('appointments:send-reminders failed.');
    });

// Mark missed appointments as no-show at 23:59 each day.
// evenInMaintenanceMode() ensures bans are applied even during deployments.
Schedule::command('appointments:mark-no-shows')
    ->dailyAt('23:59')
    ->timezone('Asia/Damascus')
    ->withoutOverlapping()
    ->onOneServer()
    ->evenInMaintenanceMode()
    ->appendOutputTo(storage_path('logs/scheduled.log'))
    ->onFailure(function () {
        Log::channel('scheduled')->error('appointments:mark-no-shows failed.');
    });

// Release expired booking bans shortly after midnight.
Schedule::command('patients:release-booking-bans')
    ->dailyAt('00:10')
    ->timezone('Asia/Damascus')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/scheduled.log'))
    ->onFailure(function () {
        Log::channel('scheduled')->error('patients:release-booking-bans failed.');
    });

// Generate monthly branch reports on the first day of each month at 01:00.
Schedule::command('reports:monthly-branch')
    ->monthlyOn(1, '01:00')
    ->timezone('Asia/Damascus')
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/scheduled.log'))
    ->onFailure(function () {
        Log::channel('scheduled')->error('reports:monthly-branch failed.');
    });
