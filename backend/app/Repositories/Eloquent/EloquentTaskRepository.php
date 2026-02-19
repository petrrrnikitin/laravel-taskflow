<?php

namespace App\Repositories\Eloquent;

use App\DTO\Task\CreateTaskDTO;
use App\DTO\Task\UpdateTaskDTO;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentTaskRepository implements TaskRepositoryInterface
{
    public function allForProject(Project $project): Collection
    {
        return Task::query()
            ->where('project_id', $project->id)
            ->with(['creator', 'assignee'])
            ->latest()
            ->get();
    }

    public function findById(int $id): ?Task
    {
        return Task::query()->with(['creator', 'assignee', 'project'])->find($id);
    }

    public function create(CreateTaskDTO $dto): Task
    {
        $task = Task::query()->create([
            'project_id'  => $dto->projectId,
            'creator_id'  => $dto->creatorId,
            'assignee_id' => $dto->assigneeId,
            'title'       => $dto->title,
            'description' => $dto->description,
            'status'      => $dto->status,
            'priority'    => $dto->priority,
            'due_date'    => $dto->dueDate,
        ]);

        return $task->fresh(['creator', 'assignee']);
    }

    public function update(Task $task, UpdateTaskDTO $dto): Task
    {
        $task->update([
            'assignee_id' => $dto->assigneeId,
            'title'       => $dto->title,
            'description' => $dto->description,
            'priority'    => $dto->priority,
            'due_date'    => $dto->dueDate,
        ]);

        return $task->fresh();
    }

    public function changeStatus(Task $task, TaskStatus $status): Task
    {
        $task->update(['status' => $status]);

        return $task->fresh();
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}