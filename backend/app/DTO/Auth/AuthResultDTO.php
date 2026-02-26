<?php

namespace App\DTO\Auth;

use App\Models\User;

final readonly class AuthResultDTO
{
    public function __construct(
        public User $user,
        public string $accessToken,
        public string $refreshToken,
        public int $expiresIn,
    ) {
    }
}
