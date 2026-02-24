<?php

namespace App\Repositories\Contracts;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface ActivityLogRepositoryInterface
{
    /** @return Collection<int, ActivityLog> */
    public function forTask(Task $task): Collection;

    /** @param array<string, mixed> $properties */
    public function log(int $taskId, int $actorId, ActivityAction $action, array $properties = []): ActivityLog;
}
