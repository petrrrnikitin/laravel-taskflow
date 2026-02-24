<?php

namespace App\Repositories\Eloquent;

use App\DTO\Task\CreateTaskDTO;
use App\DTO\Task\SearchTaskDTO;
use App\DTO\Task\UpdateTaskDTO;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function search(User $user, SearchTaskDTO $dto): LengthAwarePaginator
    {
        $accessibleProjects = Project::query()
            ->select('id')
            ->where('owner_id', $user->id)
            ->orWhereHas('members', fn ($q) => $q->where('user_id', $user->id));

        $query = Task::query()
            ->whereIn('project_id', $accessibleProjects)
            ->with(['creator', 'assignee', 'project']);

        if ($dto->q !== null) {
            $query->whereRaw(
                "search_vector @@ (plainto_tsquery('english', ?) || plainto_tsquery('russian', ?))",
                [$dto->q, $dto->q]
            )->orderByRaw(
                "ts_rank(search_vector, plainto_tsquery('english', ?) || plainto_tsquery('russian', ?)) DESC",
                [$dto->q, $dto->q]
            );
        } else {
            $query->latest();
        }

        if ($dto->status !== null) {
            $query->where('status', $dto->status->value);
        }

        if ($dto->priority !== null) {
            $query->where('priority', $dto->priority->value);
        }

        if ($dto->assigneeId !== null) {
            $query->where('assignee_id', $dto->assigneeId);
        }

        return $query->paginate($dto->perPage);
    }

    public function findById(int $id): ?Task
    {
        return Task::query()->with(['creator', 'assignee', 'project'])->find($id);
    }

    public function create(CreateTaskDTO $dto): Task
    {
        $task = Task::query()->create([
            'project_id' => $dto->projectId,
            'creator_id' => $dto->creatorId,
            'assignee_id' => $dto->assigneeId,
            'title' => $dto->title,
            'description' => $dto->description,
            'status' => $dto->status,
            'priority' => $dto->priority,
            'due_date' => $dto->dueDate,
        ]);

        return $task->fresh(['creator', 'assignee']) ?? $task;
    }

    public function update(Task $task, UpdateTaskDTO $dto): Task
    {
        $task->update([
            'assignee_id' => $dto->assigneeId,
            'title' => $dto->title,
            'description' => $dto->description,
            'priority' => $dto->priority,
            'due_date' => $dto->dueDate,
        ]);

        return $task->fresh() ?? $task;
    }

    public function changeStatus(Task $task, TaskStatus $status): Task
    {
        $task->update(['status' => $status]);

        return $task->fresh() ?? $task;
    }

    public function delete(Task $task): void
    {
        $task->delete();
    }
}
