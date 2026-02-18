<?php

namespace App\Services;

use App\DTO\Auth\LoginDTO;
use App\DTO\Auth\RegisterDTO;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
    ) {}

    public function register(RegisterDTO $dto): User
    {
        return $this->users->create($dto);
    }

    /**
     * @throws AuthenticationException
     */
    public function login(LoginDTO $dto): string
    {
        $user = $this->users->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw new AuthenticationException('Invalid credentials.');
        }

        Auth::login($user);
        $user->tokens()->delete();

        return $user->createToken('api')->plainTextToken;
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}