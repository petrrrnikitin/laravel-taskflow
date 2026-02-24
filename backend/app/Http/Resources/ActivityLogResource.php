<?php

namespace App\Http\Resources;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin ActivityLog */
#[OA\Schema(
    schema: 'ActivityLogResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'action', type: 'string', enum: ['created', 'updated', 'status_changed', 'assigned', 'deleted'], example: 'created'),
        new OA\Property(property: 'properties', type: 'object', nullable: true),
        new OA\Property(property: 'actor', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ],
)]
class ActivityLogResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action->value,
            'properties' => $this->properties,
            'actor' => new UserResource($this->whenLoaded('actor')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
