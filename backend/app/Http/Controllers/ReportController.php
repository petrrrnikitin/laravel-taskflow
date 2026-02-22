<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReportResource;
use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService,
    ) {}

    #[OA\Post(
        path: '/projects/{project}/reports',
        summary: 'Request PDF report generation for a project',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 202,
                description: 'Report generation queued',
                content: new OA\JsonContent(ref: '#/components/schemas/ReportResource'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ],
    )]
    public function store(Request $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Report::class, $project]);

        /** @var User $user */
        $user = $request->user();

        $report = $this->reportService->generate($project, $user);

        return new ReportResource($report->load('requester'))->response()->setStatusCode(202);
    }

    #[OA\Get(
        path: '/projects/{project}/reports/{report}/download',
        summary: 'Download a ready PDF report',
        security: [['bearerAuth' => []]],
        tags: ['Reports'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'report', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'PDF file', content: new OA\MediaType(mediaType: 'application/pdf')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Report not found'),
            new OA\Response(response: 409, description: 'Report is not ready yet'),
        ],
    )]
    public function download(Project $project, Report $report): StreamedResponse
    {
        $this->authorize('view', $report);

        return $this->reportService->download($report);
    }
}
