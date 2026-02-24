<?php

namespace App\Services;

use App\DTO\Auth\AuthResultDTO;
use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

readonly class AuthService
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {}

    public function register(RegisterDTO $dto): AuthResultDTO
    {
        $user = $this->users->create($dto);
        $token = $user->createToken('api')->plainTextToken;

        return new AuthResultDTO($user, $token);
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

        Auth::login($user);
        $user->tokens()->delete();

        $token = $user->createToken('api')->plainTextToken;

        return new AuthResultDTO($user, $token);
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
