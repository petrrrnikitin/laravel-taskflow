<?php

namespace App\Http\Controllers;

use App\DTO\Task\CreateTaskDTO;
use App\DTO\Task\UpdateTaskDTO;
use App\Enums\TaskStatus;
use App\Http\Requests\Task\ChangeTaskStatusRequest;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {
    }

    #[OA\Get(
        path: '/projects/{project}/tasks',
        summary: 'List tasks for a project',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of tasks',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/TaskResource'))
            ),
        ]
    )]
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        return TaskResource::collection($this->taskService->getForProject($project));
    }

    #[OA\Post(
        path : '/projects/{project}/tasks',
        summary : 'Create a task',
        security : [['bearerAuth' => []]],
        requestBody : new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Implement login page'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high']),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'assignee_id', type: 'integer', nullable: true),
                ]
            )
        ),
        tags : ['Tasks'],
        parameters : [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses : [
            new OA\Response(response: 201, description: 'Task created', content: new OA\JsonContent(ref: '#/components/schemas/TaskResource')),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(CreateTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Task::class, $project]);

        $task = $this->taskService->create(CreateTaskDTO::fromRequest($request, $project->id), $project);

        return (new TaskResource($task))->response()->setStatusCode(201);
    }

    #[OA\Get(
        path: '/projects/{project}/tasks/{task}',
        summary: 'Get a task',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Task details', content: new OA\JsonContent(ref: '#/components/schemas/TaskResource')),
            new OA\Response(response: 404, description: 'Task not found'),
        ]
    )]
    public function show(Project $project, Task $task): TaskResource
    {
        $this->authorize('view', $task);

        return new TaskResource($task);
    }

    #[OA\Put(
        path : '/projects/{project}/tasks/{task}',
        summary : 'Update a task',
        security : [['bearerAuth' => []]],
        requestBody : new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'priority'],
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high']),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'assignee_id', type: 'integer', nullable: true),
                ]
            )
        ),
        tags : ['Tasks'],
        parameters : [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses : [
            new OA\Response(response: 200, description: 'Task updated', content: new OA\JsonContent(ref: '#/components/schemas/TaskResource')),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateTaskRequest $request, Project $project, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        return new TaskResource($this->taskService->update($task, UpdateTaskDTO::fromRequest($request), $project));
    }

    #[OA\Patch(
        path : '/projects/{project}/tasks/{task}/status',
        summary : 'Change task status',
        security : [['bearerAuth' => []]],
        requestBody : new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status'],
                properties: [
                    new OA\Property(property: 'status', type: 'string', enum: ['todo', 'in_progress', 'done']),
                ]
            )
        ),
        tags : ['Tasks'],
        parameters : [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses : [
            new OA\Response(response: 200, description: 'Status updated', content: new OA\JsonContent(ref: '#/components/schemas/TaskResource')),
        ]
    )]
    public function changeStatus(ChangeTaskStatusRequest $request, Project $project, Task $task): TaskResource
    {
        $this->authorize('update', $task);

        return new TaskResource(
            $this->taskService->changeStatus($task, TaskStatus::from($request->validated('status')))
        );
    }

    #[OA\Delete(
        path: '/projects/{project}/tasks/{task}',
        summary: 'Delete a task',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Task deleted'),
            new OA\Response(response: 403, description: 'Forbidden'),
        ]
    )]
    public function destroy(Project $project, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task);

        return response()->json(null, 204);
    }
}
