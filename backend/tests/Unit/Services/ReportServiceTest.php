<?php

namespace Tests\Unit\Services;

use App\Exceptions\ReportNotReadyException;
use App\Jobs\GenerateProjectReportPdf;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use App\Repositories\Contracts\ReportRepositoryInterface;
use App\Services\ReportService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    private ReportRepositoryInterface&MockInterface $reportRepo;

    private ReportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reportRepo = $this->mock(ReportRepositoryInterface::class);
        $this->service = app(ReportService::class);
    }

    public function test_generate_creates_report_and_dispatches_job(): void
    {
        Queue::fake();

        $project = new Project();
        $project->id = 1;

        $user = new User();
        $user->id = 2;

        $report = new Report(['project_id' => 1, 'requested_by' => 2, 'status' => 'pending']);
        $report->id = 10;

        $this->reportRepo->shouldReceive('create')->with($project->id, $user->id)->once()->andReturn($report);

        $result = $this->service->generate($project, $user);

        $this->assertSame($report, $result);
        Queue::assertPushed(GenerateProjectReportPdf::class);
    }

    public function test_download_returns_streamed_response_when_report_is_ready(): void
    {
        Storage::fake();

        $filePath = 'reports/project_1/1.pdf';
        Storage::put($filePath, '%PDF-1.4 fake content');

        $report = new Report(['project_id' => 1, 'status' => 'ready', 'file_path' => $filePath]);

        $result = $this->service->download($report);

        $this->assertInstanceOf(StreamedResponse::class, $result);
    }

    public function test_download_throws_when_status_is_not_ready(): void
    {
        $report = new Report(['project_id' => 1, 'status' => 'pending', 'file_path' => 'some/path.pdf']);

        $this->expectException(ReportNotReadyException::class);

        $this->service->download($report);
    }

    public function test_download_throws_when_file_path_is_null(): void
    {
        $report = new Report(['project_id' => 1, 'status' => 'ready', 'file_path' => null]);

        $this->expectException(ReportNotReadyException::class);

        $this->service->download($report);
    }

    public function test_download_throws_when_file_does_not_exist_on_disk(): void
    {
        Storage::fake();

        $report = new Report(['project_id' => 1, 'status' => 'ready', 'file_path' => 'reports/missing.pdf']);

        $this->expectException(ReportNotReadyException::class);

        $this->service->download($report);
    }
}
