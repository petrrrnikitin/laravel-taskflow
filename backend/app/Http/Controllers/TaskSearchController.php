<?php

namespace App\Http\Controllers;

use App\DTO\Task\SearchTaskDTO;
use App\Http\Requests\Task\SearchTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class TaskSearchController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService,
    ) {}

    #[OA\Get(
        path: '/tasks/search',
        summary: 'Search tasks across all accessible projects',
        security: [['bearerAuth' => []]],
        tags: ['Tasks'],
        parameters: [
            new OA\Parameter(
                name : 'q',
                description : 'Full-text search query',
                in : 'query',
                required : false,
                schema : new OA\Schema(type: 'string')
            ),
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['todo', 'in_progress', 'done'])),
            new OA\Parameter(name: 'priority', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['low', 'medium', 'high'])),
            new OA\Parameter(name: 'assignee_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(
                name : 'per_page',
                description : 'Results per page (1–100, default 15)',
                in : 'query',
                required : false,
                schema : new OA\Schema(type: 'integer')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tasks from all accessible projects (paginated)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/TaskResource')),
                        new OA\Property(
                            property: 'links',
                            properties: [
                                new OA\Property(property: 'first', type: 'string', nullable: true),
                                new OA\Property(property: 'last', type: 'string', nullable: true),
                                new OA\Property(property: 'prev', type: 'string', nullable: true),
                                new OA\Property(property: 'next', type: 'string', nullable: true),
                            ],
                            type: 'object',
                        ),
                        new OA\Property(
                            property: 'meta',
                            properties: [
                                new OA\Property(property: 'current_page', type: 'integer'),
                                new OA\Property(property: 'from', type: 'integer', nullable: true),
                                new OA\Property(property: 'last_page', type: 'integer'),
                                new OA\Property(property: 'per_page', type: 'integer'),
                                new OA\Property(property: 'to', type: 'integer', nullable: true),
                                new OA\Property(property: 'total', type: 'integer'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function __invoke(SearchTaskRequest $request): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = $request->user();

        return TaskResource::collection(
            $this->taskService->search($user, SearchTaskDTO::fromRequest($request))
        );
    }
}
