<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\SearchUserRequest;
use App\Http\Resources\UserSearchResource;
use App\Services\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class UserSearchController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
    ) {
    }

    #[OA\Get(
        path: '/users/search',
        summary: 'Search users by name or email',
        security: [['bearerAuth' => []]],
        tags: ['Users'],
        parameters: [
            new OA\Parameter(
                name: 'q',
                description: 'Search query (min 2 characters)',
                in: 'query',
                required: true,
                schema: new OA\Schema(type: 'string', minLength: 2),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Matching users (max 10)',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/UserSearchResource')),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function __invoke(SearchUserRequest $request): AnonymousResourceCollection
    {
        return UserSearchResource::collection($this->userService->search($request->validated('q')));
    }
}
