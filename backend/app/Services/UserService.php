<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

readonly class UserService
{
    public function __construct(
        private UserRepositoryInterface $users,
    ) {
    }

    /** @return Collection<int, User> */
    public function search(string $q): Collection
    {
        return $this->users->search($q);
    }
}
