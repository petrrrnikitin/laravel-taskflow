<?php

namespace App\Jobs;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class GenerateProjectReportPdf implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(
        public readonly Report $report,
    ) {}

    public function handle(ReportRepositoryInterface $reports): void
    {
        $reports->update($this->report, ReportStatus::Processing);

        $project = $this->report->project()->with([
            'tasks.assignee',
        ])->first();

        if ($project === null) {
            throw new RuntimeException("Project not found for report #{$this->report->id}.");
        }

        $pdf = Pdf::loadView('reports.project', compact('project'));
        $path = "reports/project_{$project->id}/{$this->report->id}.pdf";

        if (! Storage::put($path, $pdf->output())) {
            throw new RuntimeException("Failed to store report PDF at {$path}.");
        }

        $reports->update($this->report, ReportStatus::Ready, $path);
    }

    public function failed(?Throwable $e): void
    {
        app(ReportRepositoryInterface::class)->update($this->report, ReportStatus::Failed);
    }
}
