<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    /** @return LengthAwarePaginator<int, DatabaseNotification> */
    public function forUser(User $user, int $perPage = 20): LengthAwarePaginator;

    public function findForUser(User $user, string $id): ?DatabaseNotification;

    public function unreadCount(User $user): int;

    public function markAllAsRead(User $user): void;
}
