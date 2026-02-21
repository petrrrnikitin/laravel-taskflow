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
        return $task->project->isOwnedBy($user) || $task->project->hasMember($user);
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->author_id === $user->id
            ?: throw new AuthorizationException('Only the comment author can edit this comment.');
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->author_id === $user->id || $comment->task->project->isOwnedBy($user)
            ?: throw new AuthorizationException('Only the comment author or project owner can delete this comment.');
    }
}