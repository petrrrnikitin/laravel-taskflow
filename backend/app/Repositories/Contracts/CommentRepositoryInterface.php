<?php

namespace App\Repositories\Contracts;

use App\DTO\Comment\CreateCommentDTO;
use App\DTO\Comment\UpdateCommentDTO;
use App\Models\Comment;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

interface CommentRepositoryInterface
{
    public function allForTask(Task $task): Collection;

    public function create(CreateCommentDTO $dto): Comment;

    public function update(Comment $comment, UpdateCommentDTO $dto): Comment;

    public function delete(Comment $comment): void;
}