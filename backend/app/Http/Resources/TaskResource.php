<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'TaskResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'project_id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Implement login page'),
        new OA\Property(
            property : 'description',
            type : 'string',
            example : 'Create login form with validation',
            nullable : true
        ),
        new OA\Property(property: 'status', type: 'string', enum: ['todo', 'in_progress', 'done'], example: 'todo'),
        new OA\Property(property: 'priority', type: 'string', enum: ['low', 'medium', 'high'], example: 'medium'),
        new OA\Property(
            property : 'due_date', type : 'string', format : 'date', example : '2026-03-01', nullable : true
        ),
        new OA\Property(property: 'creator', ref: '#/components/schemas/UserResource', nullable: true),
        new OA\Property(property: 'assignee', ref: '#/components/schemas/UserResource', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'project_id'  => $this->project_id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status,
            'priority'    => $this->priority,
            'due_date'    => $this->due_date?->toDateString(),
            'creator'     => new UserResource($this->whenLoaded('creator')),
            'assignee'    => new UserResource($this->whenLoaded('assignee')),
            'created_at'  => $this->created_at->toIso8601String(),
            'updated_at'  => $this->updated_at->toIso8601String(),
        ];
    }
}
