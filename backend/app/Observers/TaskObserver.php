<?php

namespace App\Observers;

use App\Models\Task;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class TaskObserver
{
    public function created(Task $task): void
    {
        $this->flushProjectTasksCache($task->project_id);
    }

    public function updated(Task $task): void
    {
        $this->flushProjectTasksCache($task->project_id);
    }

    public function deleting(Task $task): void
    {
        $task->comments()->delete();
        $task->activityLogs()->delete();
        $this->flushProjectTasksCache($task->project_id);
    }

    private function flushProjectTasksCache(int $projectId): void
    {
        Cache::store('redis')->tags(CacheKeys::projectTasks($projectId))->flush();
    }
}