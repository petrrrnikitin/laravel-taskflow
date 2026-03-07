<?php

namespace App\Repositories\Contracts;

use App\DTO\Auth\RegisterDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    public function create(RegisterDTO $dto): User;

    public function findByEmail(string $email): ?User;

    public function findById(int $id): ?User;

    /** @return Collection<int, User> */
    public function search(string $q): Collection;
}
