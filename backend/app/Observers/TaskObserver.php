<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    public function deleting(Task $task): void
    {
        $task->comments()->delete();
        $task->activityLogs()->delete();
    }
}