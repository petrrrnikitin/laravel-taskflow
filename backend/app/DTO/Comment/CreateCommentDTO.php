<?php

namespace App\DTO\Comment;

use App\Http\Requests\Comment\CreateCommentRequest;

final readonly class CreateCommentDTO
{
    public function __construct(
        public int $taskId,
        public int $authorId,
        public string $body,
    ) {
    }

    public static function fromRequest(CreateCommentRequest $request, int $taskId): self
    {
        $user = $request->user();
        if ($user === null) {
            abort(401);
        }

        return new self(
            taskId: $taskId,
            authorId: $user->id,
            body: $request->validated('body'),
        );
    }
}
