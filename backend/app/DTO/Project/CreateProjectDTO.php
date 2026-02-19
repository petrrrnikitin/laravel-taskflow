<?php

namespace App\DTO\Project;

use App\Http\Requests\Project\CreateProjectRequest;

final readonly class CreateProjectDTO
{
    public function __construct(
        public int $ownerId,
        public string $name,
        public ?string $description,
    ) {}

    public static function fromRequest(CreateProjectRequest $request): self
    {
        return new self(
            ownerId: $request->user()->id,
            name: $request->validated('name'),
            description: $request->validated('description'),
        );
    }
}