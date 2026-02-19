<?php

namespace App\DTO\Task;

use App\Enums\TaskPriority;
use App\Http\Requests\Task\UpdateTaskRequest;
use Carbon\Carbon;

final readonly class UpdateTaskDTO
{
    public function __construct(
        public string $title,
        public ?string $description,
        public TaskPriority $priority,
        public ?Carbon $dueDate,
        public ?int $assigneeId,
    ) {}

    public static function fromRequest(UpdateTaskRequest $request): self
    {
        return new self(
            title:       $request->validated('title'),
            description: $request->validated('description'),
            priority:    TaskPriority::from($request->validated('priority')),
            dueDate:     $request->validated('due_date')
                ? Carbon::parse($request->validated('due_date'))
                : null,
            assigneeId:  $request->validated('assignee_id'),
        );
    }
}