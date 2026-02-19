<?php

namespace App\Services;

use App\DTO\Task\CreateTaskDTO;
use App\DTO\Task\UpdateTaskDTO;
use App\Enums\TaskStatus;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;

class TaskService
{
    public function __construct(
        private readonly TaskRepositoryInterface $tasks,
    ) {}

    public function create(CreateTaskDTO $dto): Task
    {
        return $this->tasks->create($dto);
    }

    public function update(Task $task, UpdateTaskDTO $dto): Task
    {
        return $this->tasks->update($task, $dto);
    }

    public function changeStatus(Task $task, TaskStatus $status): Task
    {
        return $this->tasks->changeStatus($task, $status);
    }

    public function delete(Task $task): void
    {
        $this->tasks->delete($task);
    }
}