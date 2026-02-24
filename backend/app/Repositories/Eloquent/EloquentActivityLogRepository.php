<?php

namespace App\Repositories\Eloquent;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Task;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function forTask(Task $task): Collection
    {
        return ActivityLog::where('task_id', $task->id)
            ->with('actor')
            ->oldest()
            ->get();
    }

    /** @param array<string, mixed> $properties */
    public function log(int $taskId, int $actorId, ActivityAction $action, array $properties = []): ActivityLog
    {
        return ActivityLog::create([
            'task_id' => $taskId,
            'actor_id' => $actorId,
            'action' => $action,
            'properties' => $properties,
        ]);
    }
}
