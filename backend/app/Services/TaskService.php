<?php

namespace App\Services;

use App\DTO\Task\CreateTaskDTO;
use App\DTO\Task\SearchTaskDTO;
use App\DTO\Task\UpdateTaskDTO;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Events\TaskUpdated;
use App\Exceptions\AssigneeNotMemberException;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Repositories\Contracts\ProjectMemberRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Support\CacheKeys;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

readonly class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
        private UserRepositoryInterface $users,
        private ProjectMemberRepositoryInterface $members,
    ) {
    }

    /** @return Collection<int, Task> */
    public function getForProject(Project $project): Collection
    {
        return Cache::store('redis')->tags(CacheKeys::projectTasks($project->id))
            ->remember(
                CacheKeys::projectTasks($project->id),
                CacheKeys::TTL,
                fn () => $this->tasks->allForProject($project),
            );
    }

    /** @return LengthAwarePaginator<int, Task> */
    public function search(User $user, SearchTaskDTO $dto): LengthAwarePaginator
    {
        return $this->tasks->search($user, $dto);
    }

    public function create(CreateTaskDTO $dto, Project $project): Task
    {
        if ($dto->assigneeId !== null) {
            $this->assertAssigneeIsMember($project, $dto->assigneeId);
        }

        $task = $this->tasks->create($dto);
        $actor = Auth::user();
        if (!$actor instanceof User) {
            throw new AuthenticationException();
        }

        TaskCreated::dispatch($task, $actor);

        if ($task->assignee_id && $task->assignee) {
            TaskAssigned::dispatch($task, $task->assignee, $actor);
        }

        return $task;
    }

    public function update(Task $task, UpdateTaskDTO $dto, Project $project): Task
    {
        if ($dto->assigneeId !== null && $dto->assigneeId !== $task->assignee_id) {
            $this->assertAssigneeIsMember($project, $dto->assigneeId);
        }

        $oldAssigneeId = $task->assignee_id;
        $actor = Auth::user();
        if (!$actor instanceof User) {
            throw new AuthenticationException();
        }
        $changes = $this->trackChanges($task, $dto);

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
        /** @var TaskStatus $oldStatus */
        $oldStatus = $task->status;
        $actor = Auth::user();
        if (!$actor instanceof User) {
            throw new AuthenticationException();
        }

        $updated = $this->tasks->changeStatus($task, $status);

        TaskStatusChanged::dispatch($updated, $oldStatus, $status, $actor);

        return $updated;
    }

    public function delete(Task $task): void
    {
        $this->tasks->delete($task);
    }

    private function assertAssigneeIsMember(Project $project, int $assigneeId): void
    {
        $assignee = $this->users->findById($assigneeId);

        if ($assignee === null || !$this->members->isMember($project, $assignee)) {
            throw new AssigneeNotMemberException('Assignee must be a member of the project.');
        }
    }

    /**
     * @return array<string, array{old: mixed, new: mixed}>
     */
    private function trackChanges(Task $task, UpdateTaskDTO $dto): array
    {
        $changes = [];

        if ($dto->title !== $task->title) {
            $changes['title'] = ['old' => $task->title, 'new' => $dto->title];
        }

        if ($dto->description !== $task->description) {
            $changes['description'] = ['old' => $task->description, 'new' => $dto->description];
        }

        /** @var TaskPriority $currentPriority */
        $currentPriority = $task->priority;
        if ($dto->priority !== $currentPriority) {
            $changes['priority'] = ['old' => $currentPriority->value, 'new' => $dto->priority->value];
        }

        /** @var Carbon|null $currentDueDate */
        $currentDueDate = $task->due_date;
        if ($dto->dueDate?->toDateString() !== $currentDueDate?->toDateString()) {
            $changes['due_date'] = [
                'old' => $currentDueDate?->toDateString(),
                'new' => $dto->dueDate?->toDateString(),
            ];
        }

        return $changes;
    }
}