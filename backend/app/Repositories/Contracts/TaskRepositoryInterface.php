<?php

namespace App\Repositories\Contracts;

use App\DTO\Task\CreateTaskDTO;
use App\DTO\Task\UpdateTaskDTO;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface TaskRepositoryInterface
{
    public function allForProject(Project $project): Collection;

    public function findById(int $id): ?Task;

    public function create(CreateTaskDTO $dto): Task;

    public function update(Task $task, UpdateTaskDTO $dto): Task;

    public function changeStatus(Task $task, TaskStatus $status): Task;

    public function delete(Task $task): void;
}