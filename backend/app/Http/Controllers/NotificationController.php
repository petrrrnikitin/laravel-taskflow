<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

class NotificationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    #[OA\Get(
        path: '/notifications',
        summary: 'List notifications for authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of notifications',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/NotificationResource')),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        return NotificationResource::collection(
            $this->notificationService->getForUser($user)
        );
    }

    #[OA\Patch(
        path: '/notifications/{id}/read',
        summary: 'Mark a notification as read',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Notification marked as read'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Notification not found'),
        ],
    )]
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }
        $this->notificationService->markAsRead($user, $id);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    #[OA\Post(
        path: '/notifications/read-all',
        summary: 'Mark all notifications as read',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(response: 200, description: 'All notifications marked as read'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }
        $this->notificationService->markAllAsRead($user);

        return response()->json(['message' => 'All notifications marked as read.']);
    }

    #[OA\Get(
        path: '/notifications/unread-count',
        summary: 'Get unread notifications count',
        security: [['bearerAuth' => []]],
        tags: ['Notifications'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Unread count',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'count', type: 'integer', example: 3),
                    ],
                ),
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ],
    )]
    public function unreadCount(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        return response()->json([
            'count' => $this->notificationService->unreadCount($user),
        ]);
    }
}
