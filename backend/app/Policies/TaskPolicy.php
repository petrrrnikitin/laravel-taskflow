<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        $project = $task->project;
        if ($project === null) {
            throw new AuthorizationException('Task has no associated project.');
        }

        return $project->isOwnedBy($user) || $project->hasMember($user);
    }

    public function create(User $user, Project $project): bool
    {
        return $project->isOwnedBy($user) || $project->hasMember($user);
    }

    public function update(User $user, Task $task): bool
    {
        $project = $task->project;
        if ($project === null) {
            throw new AuthorizationException('Task has no associated project.');
        }

        return $project->isOwnedBy($user) || $project->hasMember($user);
    }

    public function delete(User $user, Task $task): bool
    {
        $project = $task->project;
        if ($project === null) {
            throw new AuthorizationException('Task has no associated project.');
        }

        return $task->creator_id === $user->id || $project->isOwnedBy($user)
            ?: throw new AuthorizationException('Only the task creator or project owner can delete this task.');
    }
}
