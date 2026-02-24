<?php

namespace App\Repositories\Contracts;

use App\Enums\ReportStatus;
use App\Models\Project;
use App\Models\Report;
use Illuminate\Database\Eloquent\Collection;

interface ReportRepositoryInterface
{
    /** @return Collection<int, Report> */
    public function allForProject(Project $project): Collection;

    public function create(int $projectId, int $requestedBy): Report;

    public function update(Report $report, ReportStatus $status, ?string $filePath = null): Report;
}
