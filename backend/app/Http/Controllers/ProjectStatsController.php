<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectStatsResource;
use App\Models\Project;
use App\Services\ProjectStatsService;
use OpenApi\Attributes as OA;

class ProjectStatsController extends Controller
{
    public function __construct(
        private readonly ProjectStatsService $statsService,
    ) {}

    #[OA\Get(
        path: '/projects/{project}/stats',
        summary: 'Get aggregated statistics for a project',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Project statistics',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ProjectStatsResource'),
                    ],
                ),
            ),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function __invoke(Project $project): ProjectStatsResource
    {
        $this->authorize('view', $project);

        return new ProjectStatsResource($this->statsService->getStats($project));
    }
}