<?php

namespace Tests\Unit\Jobs;

use App\Enums\ReportStatus;
use App\Jobs\GenerateProjectReportPdf;
use App\Models\Report;
use App\Repositories\Contracts\ReportRepositoryInterface;
use Mockery\MockInterface;
use RuntimeException;
use Tests\TestCase;

class GenerateProjectReportPdfTest extends TestCase
{
    public function test_failed_sets_report_status_to_failed(): void
    {
        $report = new Report(['project_id' => 1, 'status' => 'processing']);
        $report->id = 5;

        /** @var ReportRepositoryInterface&MockInterface $repo */
        $repo = $this->mock(ReportRepositoryInterface::class);
        $repo->shouldReceive('update')
            ->once()
            ->with($report, ReportStatus::Failed);

        $job = new GenerateProjectReportPdf($report);
        $job->failed(new RuntimeException('Storage failure'));
    }

    public function test_failed_accepts_null_throwable(): void
    {
        $report = new Report(['project_id' => 1, 'status' => 'processing']);
        $report->id = 6;

        $this->mock(ReportRepositoryInterface::class)
            ->shouldReceive('update')
            ->once()
            ->with($report, ReportStatus::Failed);

        $job = new GenerateProjectReportPdf($report);

        $job->failed(null);

        $this->addToAssertionCount(1);
    }
}
