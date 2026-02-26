<?php

namespace App\Services;

use App\DTO\Auth\AuthResultDTO;
use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Models\User;
use App\Repositories\Contracts\TokenRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

readonly class AuthService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private TokenRepositoryInterface $tokens,
    ) {
    }

    public function register(RegisterDTO $dto): AuthResultDTO
    {
        $user = $this->users->create($dto);

        return $this->issueTokenPair($user);
    }

    /**
     * @throws AuthenticationException
     */
    public function login(LoginDTO $dto): AuthResultDTO
    {
        $user = $this->users->findByEmail($dto->email);

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $user->tokens()->delete();

        return $this->issueTokenPair($user);
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }

    /**
     * @throws AuthenticationException
     */
    public function refresh(string $rawToken): AuthResultDTO
    {
        $token = $this->tokens->consumeRefreshToken($rawToken);

        if (! $token) {
            throw new AuthenticationException('Invalid or expired refresh token.');
        }

        $user = $token->tokenable;

        if (! $user instanceof User) {
            throw new AuthenticationException('Invalid refresh token.');
        }

        return $this->issueTokenPair($user);
    }

    private function issueTokenPair(User $user): AuthResultDTO
    {
        $accessTtl  = (int) config('sanctum.access_token_ttl');
        $refreshTtl = (int) config('sanctum.refresh_token_ttl');

        $access = $user->createToken('access', ['*'], now()->addMinutes($accessTtl));
        $refresh = $user->createToken('refresh', ['refresh'], now()->addDays($refreshTtl));

        return new AuthResultDTO(
            $user,
            $access->plainTextToken,
            $refresh->plainTextToken,
            $accessTtl * 60,
        );
    }
}
