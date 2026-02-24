<?php

namespace App\Http\Controllers;

use App\DTO\Member\AddMemberDTO;
use App\Http\Requests\Member\AddMemberRequest;
use App\Http\Resources\ProjectMemberResource;
use App\Models\Project;
use App\Models\User;
use App\Services\ProjectMemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class ProjectMemberController extends Controller
{
    public function __construct(
        private readonly ProjectMemberService $memberService,
    ) {
    }

    #[OA\Get(
        path: '/projects/{project}/members',
        summary: 'List project members',
        security: [['bearerAuth' => []]],
        tags: ['Members'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of members',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/ProjectMemberResource')),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Not a project member'),
        ],
    )]
    public function index(Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        return ProjectMemberResource::collection($this->memberService->getMembers($project));
    }

    #[OA\Post(
        path: '/projects/{project}/members',
        summary: 'Invite a user to the project',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['user_id'],
                properties: [
                    new OA\Property(property: 'user_id', type: 'integer', example: 2),
                ],
            ),
        ),
        tags: ['Members'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Member added',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Member added successfully.'),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Only the project owner can invite members'),
            new OA\Response(response: 404, description: 'User not found'),
            new OA\Response(response: 409, description: 'User is already a member'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(AddMemberRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $dto = AddMemberDTO::fromRequest($request);

        /** @var User $actor */
        $actor = $request->user();

        $this->memberService->invite($project, $dto->userId, $actor);

        return response()->json(['message' => 'Member added successfully.'], 201);
    }

    #[OA\Delete(
        path: '/projects/{project}/members/{user}',
        summary: 'Remove a member from the project',
        security: [['bearerAuth' => []]],
        tags: ['Members'],
        parameters: [
            new OA\Parameter(name: 'project', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Member removed'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 403, description: 'Only the project owner can remove members'),
            new OA\Response(response: 422, description: 'Cannot remove the project owner'),
        ],
    )]
    public function destroy(Project $project, User $user): JsonResponse
    {
        $this->authorize('update', $project);

        $this->memberService->remove($project, $user);

        return response()->json(null, 204);
    }
}
