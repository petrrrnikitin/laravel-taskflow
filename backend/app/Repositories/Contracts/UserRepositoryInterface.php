<?php

namespace App\Repositories\Contracts;

use App\DTO\Auth\RegisterDTO;
use App\Models\User;

interface UserRepositoryInterface
{
    public function create(RegisterDTO $dto): User;

    public function findByEmail(string $email): ?User;
}