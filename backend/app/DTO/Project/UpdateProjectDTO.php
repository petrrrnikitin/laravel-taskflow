<?php

namespace App\DTO\Project;

use App\Http\Requests\Project\UpdateProjectRequest;

final readonly class UpdateProjectDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
    ) {
    }

    public static function fromRequest(UpdateProjectRequest $request): self
    {
        return new self(
            name: $request->validated('name'),
            description: $request->validated('description'),
        );
    }
}
