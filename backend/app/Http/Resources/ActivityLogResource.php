<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'action'     => $this->action->value,
            'properties' => $this->properties,
            'actor'      => new UserResource($this->whenLoaded('actor')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}