<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;

interface TokenRepositoryInterface
{
    public function consumeRefreshToken(string $plainTextToken): ?PersonalAccessToken;

    public function deleteForUser(User $user): void;

    /** @param array<string> $abilities */
    public function createToken(User $user, string $name, array $abilities, Carbon $expiresAt): NewAccessToken;
}
