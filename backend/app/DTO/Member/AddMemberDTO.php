<?php

namespace App\DTO\Member;

use App\Http\Requests\Member\AddMemberRequest;

final readonly class AddMemberDTO
{
    public function __construct(
        public int $userId,
    ) {
    }

    public static function fromRequest(AddMemberRequest $request): self
    {
        return new self(
            userId: $request->validated('user_id'),
        );
    }
}
