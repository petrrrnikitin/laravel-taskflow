<?php

namespace App\Http\Controllers;

use App\DTO\Project\CreateProjectDTO;
use App\DTO\Project\UpdateProjectDTO;
use App\Http\Requests\Project\CreateProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectService $projectService,
        private readonly ProjectRepositoryInterface $projects,
    ) {}

    #[OA\Get(
        path: '/projects',
        summary: 'List all projects for authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of projects',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ProjectResource'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $projects = $this->projects->allForUser($request->user());

        return ProjectResource::collection($projects);
    }

    #[OA\Post(
        path : '/projects',
        summary : 'Create a new project',
        security : [['bearerAuth' => []]],
        requestBody : new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'My Project'),
                    new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Project description'),
                ],
            ),
        ),
        tags : ['Projects'],
        responses : [
            new OA\Response(response: 201, description: 'Project created', content: new OA\JsonContent(ref: '#/components/schemas/ProjectResource')),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(CreateProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create(CreateProjectDTO::fromRequest($request));

        return new ProjectResource($project->load('owner'))
            ->response()
            ->setStatusCode(201);
    }

    #[OA\Get(
        path: '/projects/{id}',
        summary: 'Get a project by ID',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Project data', content: new OA\JsonContent(ref: '#/components/schemas/ProjectResource')),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Not found'),
        ],
    )]
    public function show(Request $request, Project $project): ProjectResource
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load('owner', 'members'));
    }

    #[OA\Put(
        path : '/projects/{id}',
        summary : 'Update a project',
        security : [['bearerAuth' => []]],
        requestBody : new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Updated name'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                ],
            ),
        ),
        tags : ['Projects'],
        parameters : [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses : [
            new OA\Response(response: 200, description: 'Project updated', content: new OA\JsonContent(ref: '#/components/schemas/ProjectResource')),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function update(UpdateProjectRequest $request, Project $project): ProjectResource
    {
        $this->authorize('update', $project);

        $project = $this->projectService->update($project, UpdateProjectDTO::fromRequest($request));

        return new ProjectResource($project->load('owner'));
    }

    #[OA\Post(
        path: '/projects/{id}/archive',
        summary: 'Archive a project',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Project archived', content: new OA\JsonContent(ref: '#/components/schemas/ProjectResource')),
            new OA\Response(response: 403, description: 'Forbidden'),
        ],
    )]
    public function archive(Request $request, Project $project): ProjectResource
    {
        $this->authorize('update', $project);

        $project = $this->projectService->archive($project);

        return new ProjectResource($project->load('owner'));
    }

    #[OA\Delete(
        path: '/projects/{id}',
        summary: 'Delete a project',
        security: [['bearerAuth' => []]],
        tags: ['Projects'],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Project deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ],
    )]
    public function destroy(Request $request, Project $project): JsonResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->delete($project);

        return response()->json(null, 204);
    }
}
