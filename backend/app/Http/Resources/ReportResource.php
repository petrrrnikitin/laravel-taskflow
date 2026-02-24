<?php

namespace App\Http\Resources;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

/** @mixin Report */
#[OA\Schema(
    schema: 'ReportResource',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'project_id', type: 'integer', example: 1),
        new OA\Property(property: 'status', type: 'string', enum: ['pending', 'processing', 'ready', 'failed'], example: 'pending'),
        new OA\Property(property: 'requester', ref: '#/components/schemas/UserResource', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class ReportResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'status' => $this->status->value,
            'requester' => new UserResource($this->whenLoaded('requester')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
