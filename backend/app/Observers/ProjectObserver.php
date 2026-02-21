<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Task;

class ProjectObserver
{
    public function deleting(Project $project): void
    {
        $project->tasks()->each(fn(Task $task) => $task->delete());
        $project->members()->detach();
    }
}