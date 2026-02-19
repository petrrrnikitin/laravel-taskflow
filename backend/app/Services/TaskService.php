<?php

namespace App\Services;

use App\DTO\Task\CreateTaskDTO;
use App\DTO\Task\UpdateTaskDTO;
use App\Enums\TaskStatus;
use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Events\TaskUpdated;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

readonly class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
    ) {}

    public function getForProject(Project $project): Collection
    {
        return $this->tasks->allForProject($project);
    }

    public function create(CreateTaskDTO $dto): Task
    {
        $task  = $this->tasks->create($dto);
        $actor = Auth::user();

        TaskCreated::dispatch($task, $actor);

        if ($task->assignee_id && $task->assignee) {
            TaskAssigned::dispatch($task, $task->assignee, $actor);
        }

        return $task;
    }

    public function update(Task $task, UpdateTaskDTO $dto): Task
    {
        $oldAssigneeId = $task->assignee_id;
        $actor         = Auth::user();
        $changes       = $this->trackChanges($task, $dto);

        $updated = $this->tasks->update($task, $dto);

        if (!empty($changes)) {
            TaskUpdated::dispatch($updated, $changes, $actor);
        }

        if ($dto->assigneeId && $dto->assigneeId !== $oldAssigneeId) {
            $updated->load('assignee');
            if ($updated->assignee) {
                TaskAssigned::dispatch($updated, $updated->assignee, $actor);
            }
        }

        return $updated;
    }

    public function changeStatus(Task $task, TaskStatus $status): Task
    {
        $oldStatus = $task->status;
        $actor     = Auth::user();

        $updated = $this->tasks->changeStatus($task, $status);

        TaskStatusChanged::dispatch($updated, $oldStatus, $status, $actor);

        return $updated;
    }

    public function delete(Task $task): void
    {
        $this->tasks->delete($task);
    }

    private function trackChanges(Task $task, UpdateTaskDTO $dto): array
    {
        $changes = [];

        if ($dto->title !== $task->title) {
            $changes['title'] = ['old' => $task->title, 'new' => $dto->title];
        }

        if ($dto->description !== $task->description) {
            $changes['description'] = ['old' => $task->description, 'new' => $dto->description];
        }

        if ($dto->priority !== $task->priority) {
            $changes['priority'] = ['old' => $task->priority->value, 'new' => $dto->priority->value];
        }

        if ($dto->dueDate?->toDateString() !== $task->due_date?->toDateString()) {
            $changes['due_date'] = [
                'old' => $task->due_date?->toDateString(),
                'new' => $dto->dueDate?->toDateString(),
            ];
        }

        return $changes;
    }
}
