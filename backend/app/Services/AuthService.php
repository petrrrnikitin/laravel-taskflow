<?php

namespace App\Services;

use App\Config\SanctumConfig;
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
        private SanctumConfig $sanctumConfig,
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

        $this->tokens->deleteForUser($user);

        return $this->issueTokenPair($user);
    }

    public function logout(User $user): void
    {
        $this->tokens->deleteForUser($user);
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
        $access  = $this->tokens->createToken($user, 'access', ['*'], now()->addMinutes($this->sanctumConfig->accessTokenTtl));
        $refresh = $this->tokens->createToken($user, 'refresh', ['refresh'], now()->addDays($this->sanctumConfig->refreshTokenTtl));

        return new AuthResultDTO(
            $user,
            $access->plainTextToken,
            $refresh->plainTextToken,
            $this->sanctumConfig->accessTokenTtl * 60,
        );
    }
}
