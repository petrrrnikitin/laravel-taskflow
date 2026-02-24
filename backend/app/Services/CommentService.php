<?php

namespace App\Services;

use App\DTO\Comment\CreateCommentDTO;
use App\DTO\Comment\UpdateCommentDTO;
use App\Models\Comment;
use App\Models\Task;
use App\Repositories\Contracts\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

readonly class CommentService
{
    public function __construct(
        private CommentRepositoryInterface $comments,
    ) {}

    /** @return Collection<int, Comment> */
    public function getForTask(Task $task): Collection
    {
        return $this->comments->allForTask($task);
    }

    public function create(CreateCommentDTO $dto): Comment
    {
        return $this->comments->create($dto);
    }

    public function update(Comment $comment, UpdateCommentDTO $dto): Comment
    {
        return $this->comments->update($comment, $dto);
    }

    public function delete(Comment $comment): void
    {
        $this->comments->delete($comment);
    }
}
