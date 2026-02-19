<?php

namespace App\Repositories\Contracts;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface ActivityLogRepositoryInterface
{
    public function forTask(Task $task): Collection;

    public function log(int $taskId, int $actorId, ActivityAction $action, array $properties = []): ActivityLog;
}