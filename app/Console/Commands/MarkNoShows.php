<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Exceptions\BookingException;
use App\Models\Appointment;
use App\Services\AppointmentBookingService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkNoShows extends Command
{
    protected $signature = 'appointments:mark-no-shows
                            {--date= : Mark appointments before this date as no-show (defaults to today)}
                            {--dry-run : Preview without making changes}';

    protected $description = 'Mark past booked appointments as no-show and apply booking bans where needed';

    public function __construct(private AppointmentBookingService $bookingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $before = $this->option('date')
            ? Carbon::parse($this->option('date'))->startOfDay()
            : now()->startOfDay();

        $dryRun = $this->option('dry-run');

        $this->info('Marking appointments before: ' . $before->format('Y-m-d H:i') . ($dryRun ? ' [dry-run]' : ''));

        $appointments = Appointment::where('status', AppointmentStatus::Booked->value)
            ->where('end_at', '<', $before)
            ->with('patient')
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No missed appointments found.');
            return self::SUCCESS;
        }

        $marked = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            $this->line("  " . ($dryRun ? '[dry-run] Would mark' : 'Marking') . " {$appointment->reference} as no-show.");

            if ($dryRun) {
                $marked++;
                continue;
            }

            try {
                $this->bookingService->markNoShow($appointment);
                $marked++;
            } catch (BookingException $e) {
                $this->warn("  Failed {$appointment->reference}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Done. Marked: {$marked}, Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
