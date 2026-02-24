<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class CommentPolicy
{
    public function create(User $user, Task $task): bool
    {
        $project = $task->project;
        if ($project === null) {
            throw new AuthorizationException('Task has no associated project.');
        }

        return $project->isOwnedBy($user) || $project->hasMember($user);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->author_id === $user->id
            ?: throw new AuthorizationException('Only the comment author can edit this comment.');
    }

    public function delete(User $user, Comment $comment): bool
    {
        $project = $comment->task?->project;
        if ($project === null) {
            throw new AuthorizationException('Comment has no associated project.');
        }

        return $comment->author_id === $user->id || $project->isOwnedBy($user)
            ?: throw new AuthorizationException('Only the comment author or project owner can delete this comment.');
    }
}
