<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Jobs\SendAppointmentReminderSms;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders
                            {--date= : Target date in Y-m-d format (defaults to tomorrow)}
                            {--dry-run : Preview without dispatching jobs or updating records}';

    protected $description = 'Send SMS reminders for appointments scheduled on the target date';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : now()->addDay()->startOfDay();

        $dryRun = $this->option('dry-run');

        $this->info('Target date: ' . $date->format('Y-m-d') . ($dryRun ? ' [dry-run]' : ''));

        $appointments = Appointment::whereDate('start_at', $date)
            ->whereIn('status', [AppointmentStatus::Booked->value, AppointmentStatus::Arrived->value])
            ->whereNull('reminder_sent_at')
            ->with('patient')
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments need reminders for this date.');
            return self::SUCCESS;
        }

        $count = 0;

        foreach ($appointments as $appointment) {
            if (!$appointment->patient || !$appointment->patient->phone) {
                $this->line("  Skipped #{$appointment->reference} — no patient phone.");
                continue;
            }

            $this->line("  " . ($dryRun ? '[dry-run] Would send' : 'Dispatching') . " reminder for {$appointment->reference} → {$appointment->patient->phone}");

            if (!$dryRun) {
                SendAppointmentReminderSms::dispatch($appointment);
                $appointment->update(['reminder_sent_at' => now()]);
            }

            $count++;
        }

        $this->info("Done. {$count} reminder(s) " . ($dryRun ? 'would be sent.' : 'dispatched.'));

        return self::SUCCESS;
    }
}
