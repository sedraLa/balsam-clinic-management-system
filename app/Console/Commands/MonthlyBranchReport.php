<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MonthlyBranchReport extends Command
{
    protected $signature = 'reports:monthly-branch
                            {--month= : Month in YYYY-MM format (defaults to last month)}
                            {--branch= : Branch ID to report on (defaults to all branches)}
                            {--format=json : Output format: json or csv}';

    protected $description = 'Generate a monthly appointments report per branch';

    public function handle(): int
    {
        $monthInput = $this->option('month') ?? now()->subMonth()->format('Y-m');

        if (!preg_match('/^\d{4}-\d{2}$/', $monthInput)) {
            $this->error('Invalid --month format. Use YYYY-MM (e.g. 2026-11).');
            return self::FAILURE;
        }

        $format = $this->option('format');

        if (!in_array($format, ['json', 'csv'])) {
            $this->error('Invalid --format. Use "json" or "csv".');
            return self::FAILURE;
        }

        $start = Carbon::parse($monthInput . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $branchQuery = Branch::query();

        if ($this->option('branch')) {
            $branchId = (int) $this->option('branch');
            $branchQuery->where('id', $branchId);

            if ($branchQuery->doesntExist()) {
                $this->error("Branch ID {$branchId} not found.");
                return self::FAILURE;
            }
        }

        $branches = $branchQuery->get();
        $reports  = [];

        foreach ($branches as $branch) {
            $base = Appointment::where('branch_id', $branch->id)
                ->whereBetween('start_at', [$start, $end]);

            $reports[] = [
                'branch_id'   => $branch->id,
                'branch_name' => $branch->name,
                'branch_code' => $branch->code,
                'month'       => $monthInput,
                'total'       => (clone $base)->count(),
                'booked'      => (clone $base)->where('status', AppointmentStatus::Booked->value)->count(),
                'arrived'     => (clone $base)->where('status', AppointmentStatus::Arrived->value)->count(),
                'completed'   => (clone $base)->where('status', AppointmentStatus::Completed->value)->count(),
                'cancelled'   => (clone $base)->where('status', AppointmentStatus::Cancelled->value)->count(),
                'no_show'     => (clone $base)->where('status', AppointmentStatus::NoShow->value)->count(),
            ];
        }

        $filename  = "reports/monthly-branch_{$monthInput}.{$format}";
        $contents  = $format === 'json'
            ? json_encode($reports, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            : $this->toCsv($reports);

        Storage::disk('local')->put($filename, $contents);

        $path = storage_path('app/' . $filename);
        $this->info("Report saved to: {$path}");

        $this->table(
            ['Branch', 'Total', 'Booked', 'Arrived', 'Completed', 'Cancelled', 'No-show'],
            array_map(fn($r) => [
                $r['branch_name'],
                $r['total'],
                $r['booked'],
                $r['arrived'],
                $r['completed'],
                $r['cancelled'],
                $r['no_show'],
            ], $reports)
        );

        return self::SUCCESS;
    }

    private function toCsv(array $reports): string
    {
        if (empty($reports)) {
            return '';
        }

        $lines   = [];
        $lines[] = implode(',', array_keys($reports[0]));

        foreach ($reports as $row) {
            $lines[] = implode(',', array_values($row));
        }

        return implode("\n", $lines);
    }
}
