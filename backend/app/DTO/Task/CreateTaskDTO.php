<?php

namespace App\DTO\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\Task\CreateTaskRequest;
use Carbon\Carbon;

final readonly class CreateTaskDTO
{
    public function __construct(
        public int $projectId,
        public int $creatorId,
        public string $title,
        public ?string $description,
        public TaskStatus $status,
        public TaskPriority $priority,
        public ?Carbon $dueDate,
        public ?int $assigneeId,
    ) {}

    public static function fromRequest(CreateTaskRequest $request, int $projectId): self
    {
        return new self(
            projectId:   $projectId,
            creatorId:   $request->user()->id,
            title:       $request->validated('title'),
            description: $request->validated('description'),
            status:      TaskStatus::Todo,
            priority:    TaskPriority::from($request->validated('priority', TaskPriority::Medium->value)),
            dueDate:     $request->validated('due_date')
                ? Carbon::parse($request->validated('due_date'))
                : null,
            assigneeId:  $request->validated('assignee_id'),
        );
    }
}