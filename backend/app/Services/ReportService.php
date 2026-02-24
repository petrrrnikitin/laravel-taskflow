<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Exceptions\ReportNotReadyException;
use App\Jobs\GenerateProjectReportPdf;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

readonly class ReportService
{
    public function __construct(
        private ReportRepositoryInterface $reports,
    ) {
    }

    public function generate(Project $project, User $user): Report
    {
        $report = $this->reports->create($project->id, $user->id);

        GenerateProjectReportPdf::dispatch($report);

        return $report;
    }

    public function download(Report $report): StreamedResponse
    {
        if ($report->status !== ReportStatus::Ready
            || $report->file_path === null
            || ! Storage::exists($report->file_path)
        ) {
            throw new ReportNotReadyException('Report is not ready yet.');
        }

        return Storage::download(
            $report->file_path,
            "project-{$report->project_id}-report.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }
}
