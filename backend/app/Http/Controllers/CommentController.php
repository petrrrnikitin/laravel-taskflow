<?php

namespace App\Http\Controllers;

use App\DTO\Comment\CreateCommentDTO;
use App\DTO\Comment\UpdateCommentDTO;
use App\Http\Requests\Comment\CreateCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class CommentController extends Controller
{
    public function __construct(
        private readonly CommentService $commentService,
    ) {}

    #[OA\Get(
        path: '/projects/{project}/tasks/{task}/comments',
        summary: 'List comments for a task',
        security: [['bearerAuth' => []]],
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of comments',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/CommentResource')),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Task not found'),
        ],
    )]
    public function index(Project $project, Task $task): AnonymousResourceCollection
    {
        $this->authorize('view', $task);

        return CommentResource::collection($this->commentService->getForTask($task));
    }

    #[OA\Post(
        path: '/projects/{project}/tasks/{task}/comments',
        summary: 'Add a comment to a task',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['body'],
                properties: [
                    new OA\Property(property: 'body', type: 'string', example: 'Looks good!'),
                ],
            ),
        ),
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Comment created',
                content: new OA\JsonContent(ref: '#/components/schemas/CommentResource'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Forbidden'),
            new OA\Response(response: 404, description: 'Task not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(CreateCommentRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('create', [Comment::class, $task]);

        $comment = $this->commentService->create(CreateCommentDTO::fromRequest($request, $task->id));

        return new CommentResource($comment)->response()->setStatusCode(201);
    }

    #[OA\Patch(
        path: '/projects/{project}/tasks/{task}/comments/{comment}',
        summary: 'Update a comment',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['body'],
                properties: [
                    new OA\Property(property: 'body', type: 'string', example: 'Updated text'),
                ],
            ),
        ),
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'comment', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Comment updated',
                content: new OA\JsonContent(ref: '#/components/schemas/CommentResource'),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Only the comment author can edit'),
            new OA\Response(response: 404, description: 'Comment not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function update(UpdateCommentRequest $request, Project $project, Task $task, Comment $comment): CommentResource
    {
        $this->authorize('update', $comment);

        $comment = $this->commentService->update($comment, UpdateCommentDTO::fromRequest($request));

        return new CommentResource($comment);
    }

    #[OA\Delete(
        path: '/projects/{project}/tasks/{task}/comments/{comment}',
        summary: 'Delete a comment',
        security: [['bearerAuth' => []]],
        tags: ['Comments'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'task', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'comment', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Comment deleted'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Only the comment author or project owner can delete'),
            new OA\Response(response: 404, description: 'Comment not found'),
        ],
    )]
    public function destroy(Project $project, Task $task, Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->delete($comment);

        return response()->json(null, 204);
    }
}
