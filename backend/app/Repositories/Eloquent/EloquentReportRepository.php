<?php

namespace App\Repositories\Eloquent;

use App\Enums\ReportStatus;
use App\Models\Project;
use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentReportRepository implements ReportRepositoryInterface
{
    public function allForProject(Project $project): Collection
    {
        return Report::query()
            ->where('project_id', $project->id)
            ->with('requester')
            ->latest()
            ->get();
    }

    public function create(int $projectId, int $requestedBy): Report
    {
        return Report::query()->create([
            'project_id'   => $projectId,
            'requested_by' => $requestedBy,
            'status'       => ReportStatus::Pending,
        ]);
    }

    public function update(Report $report, ReportStatus $status, ?string $filePath = null): Report
    {
        $report->update(array_filter([
            'status'    => $status,
            'file_path' => $filePath,
        ], static fn($v) => $v !== null));

        return $report->fresh() ?? throw new \RuntimeException("Report #{$report->id} not found after update.");
    }
}
