<?php

namespace App\Repositories\Contracts;

use Laravel\Sanctum\PersonalAccessToken;

interface TokenRepositoryInterface
{
    public function consumeRefreshToken(string $plainTextToken): ?PersonalAccessToken;
}
