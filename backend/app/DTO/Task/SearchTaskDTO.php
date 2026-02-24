<?php

namespace App\DTO\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\Task\SearchTaskRequest;

final readonly class SearchTaskDTO
{
    public function __construct(
        public ?string $q,
        public ?TaskStatus $status,
        public ?TaskPriority $priority,
        public ?int $assigneeId,
        public int $perPage,
    ) {
    }

    public static function fromRequest(SearchTaskRequest $request): self
    {
        return new self(
            q: $request->filled('q') ? trim((string) $request->validated('q')) : null,
            status: $request->filled('status') ? TaskStatus::from($request->validated('status')) : null,
            priority: $request->filled('priority') ? TaskPriority::from($request->validated('priority')) : null,
            assigneeId: $request->filled('assignee_id') ? (int) $request->validated('assignee_id') : null,
            perPage: (int) ($request->validated('per_page') ?? 15),
        );
    }
}
