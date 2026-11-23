<?php

namespace App\Console\Commands;

use App\Models\Patient;
use Illuminate\Console\Command;

class ReleaseBookingBans extends Command
{
    protected $signature = 'patients:release-booking-bans
                            {--dry-run : Preview without making changes}';

    protected $description = 'Clear expired booking bans from patients';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $patients = Patient::whereNotNull('booking_banned_until')
            ->where('booking_banned_until', '<=', now())
            ->get();

        if ($patients->isEmpty()) {
            $this->info('No expired bans found.');
            return self::SUCCESS;
        }

        foreach ($patients as $patient) {
            $this->line("  " . ($dryRun ? '[dry-run] Would release' : 'Releasing') . " ban for patient #{$patient->id} ({$patient->full_name}), expired: {$patient->booking_banned_until->format('Y-m-d')}");

            if (!$dryRun) {
                $patient->update(['booking_banned_until' => null]);
            }
        }

        $this->info('Done. ' . $patients->count() . ' ban(s) ' . ($dryRun ? 'would be released.' : 'released.'));

        return self::SUCCESS;
    }
}
