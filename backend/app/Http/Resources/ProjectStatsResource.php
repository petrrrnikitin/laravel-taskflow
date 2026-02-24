<?php

namespace App\Http\Resources;

use App\DTO\Project\ProjectStats;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProjectStatsResource',
    properties: [
        new OA\Property(
            property: 'tasks',
            properties: [
                new OA\Property(property: 'total', type: 'integer', example: 12),
                new OA\Property(
                    property: 'by_status',
                    properties: [
                        new OA\Property(property: 'todo', type: 'integer', example: 5),
                        new OA\Property(property: 'in_progress', type: 'integer', example: 4),
                        new OA\Property(property: 'done', type: 'integer', example: 3),
                    ],
                    type: 'object',
                ),
                new OA\Property(
                    property: 'by_priority',
                    properties: [
                        new OA\Property(property: 'low', type: 'integer', example: 3),
                        new OA\Property(property: 'medium', type: 'integer', example: 6),
                        new OA\Property(property: 'high', type: 'integer', example: 3),
                    ],
                    type: 'object',
                ),
                new OA\Property(property: 'overdue', type: 'integer', example: 2),
            ],
            type: 'object',
        ),
        new OA\Property(property: 'completion_rate', type: 'number', format: 'float', example: 25.0),
        new OA\Property(property: 'members_count', type: 'integer', example: 4),
        new OA\Property(
            property: 'top_assignees',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'id', type: 'integer', example: 1),
                    new OA\Property(property: 'name', type: 'string', example: 'Alice'),
                    new OA\Property(property: 'avatar', type: 'string', example: null, nullable: true),
                    new OA\Property(property: 'closed_tasks_count', type: 'integer', example: 5),
                ],
                type: 'object',
            ),
        ),
    ],
)]
class ProjectStatsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var ProjectStats $stats */
        $stats = $this->resource;

        return [
            'tasks' => [
                'total' => $stats->totalTasks,
                'by_status' => $stats->byStatus,
                'by_priority' => $stats->byPriority,
                'overdue' => $stats->overdueTasks,
            ],
            'completion_rate' => $stats->completionRate,
            'members_count' => $stats->membersCount,
            'top_assignees' => $stats->topAssignees->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'avatar' => $u->avatar,
                'closed_tasks_count' => $u->closed_tasks_count,
            ])->values()->all(),
        ];
    }
}
