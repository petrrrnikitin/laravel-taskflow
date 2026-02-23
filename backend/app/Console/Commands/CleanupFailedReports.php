<?php

namespace App\Console\Commands;

use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupFailedReports extends Command
{
    protected $signature = 'reports:cleanup {--days=30 : Delete failed reports older than this many days}';

    protected $description = 'Delete failed report records and their associated files older than N days';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error('--days must be a positive integer (got: ' . $days . ').');
            return self::INVALID;
        }

        $count = 0;

        Report::query()
            ->where('status', ReportStatus::Failed)
            ->where('created_at', '<', Carbon::now()->subDays($days))
            ->chunkById(100, function ($reports) use (&$count): void {
                foreach ($reports as $report) {
                    if ($report->file_path !== null && Storage::exists($report->file_path)) {
                        if (!Storage::delete($report->file_path)) {
                            Log::warning('Could not delete report file, skipping record.', [
                                'report_id' => $report->id,
                                'file_path' => $report->file_path,
                            ]);
                            continue;
                        }
                    }

                    $report->delete();
                    $count++;
                }
            });

        $this->info("Deleted {$count} failed report(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}