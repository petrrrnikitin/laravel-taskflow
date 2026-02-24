<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

readonly class ActivityService
{
    public function __construct(
        private ActivityLogRepositoryInterface $activityLogs,
    ) {
    }

    /** @return Collection<int, ActivityLog> */
    public function getForTask(Task $task): Collection
    {
        return $this->activityLogs->forTask($task);
    }
}
