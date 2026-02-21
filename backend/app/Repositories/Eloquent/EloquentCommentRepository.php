<?php

namespace App\Repositories\Eloquent;

use App\DTO\Comment\CreateCommentDTO;
use App\DTO\Comment\UpdateCommentDTO;
use App\Models\Comment;
use App\Models\Task;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function allForTask(Task $task): Collection
    {
        return Comment::query()
            ->where('task_id', $task->id)
            ->with('author')
            ->oldest()
            ->get();
    }

    public function create(CreateCommentDTO $dto): Comment
    {
        $comment = Comment::query()->create([
            'task_id'   => $dto->taskId,
            'author_id' => $dto->authorId,
            'body'      => $dto->body,
        ]);

        return $comment->load('author');
    }

    public function update(Comment $comment, UpdateCommentDTO $dto): Comment
    {
        $comment->update(['body' => $dto->body]);

        return $comment->fresh();
    }

    public function delete(Comment $comment): void
    {
        $comment->delete();
    }
}