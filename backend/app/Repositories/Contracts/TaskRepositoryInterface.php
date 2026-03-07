<?php

namespace App\Repositories\Contracts;

use App\DTO\Task\CreateTaskDTO;
use App\DTO\Task\SearchTaskDTO;
use App\DTO\Task\UpdateTaskDTO;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

interface TaskRepositoryInterface
{
    /** @return Collection<int, Task> */
    public function allForProject(Project $project): Collection;

    /** @return LengthAwarePaginator<int, Task> */
    public function search(User $user, SearchTaskDTO $dto): LengthAwarePaginator;

    public function findById(int $id): ?Task;

    public function create(CreateTaskDTO $dto): Task;

    public function update(Task $task, UpdateTaskDTO $dto): Task;

    public function changeStatus(Task $task, TaskStatus $status): Task;

    public function delete(Task $task): void;

    /** @return array<string, int> */
    public function countByStatusForProject(Project $project): array;

    /** @return array<string, int> */
    public function countByPriorityForProject(Project $project): array;

    public function countOverdueForProject(Project $project): int;

    /** @return BaseCollection<int, object{id: int, name: string, avatar: string|null, closed_tasks_count: int}> */
    public function topAssigneesForProject(Project $project, int $limit): BaseCollection;
}
