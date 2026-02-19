<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProjectResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'My Project'),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'status', type: 'string', enum: ['active', 'archived'], example: 'active'),
        new OA\Property(property: 'owner', ref: '#/components/schemas/UserResource'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ],
)]
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'owner'       => new UserResource($this->whenLoaded('owner')),
            'created_at'  => $this->created_at->toIso8601String(),
        ];
    }
}