<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Notifications\DatabaseNotification;
use OpenApi\Attributes as OA;

/** @mixin DatabaseNotification */
#[OA\Schema(
    schema: 'NotificationResource',
    properties: [
        new OA\Property(property: 'id', type: 'string', format: 'uuid', example: '01959b29-9e3d-7000-a71f-ddb5f8bd4c09'),
        new OA\Property(property: 'type', type: 'string', example: 'TaskActivityNotification'),
        new OA\Property(property: 'data', type: 'object'),
        new OA\Property(property: 'read_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
    ],
)]
class NotificationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => class_basename($this->type),
            'data' => $this->data,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
