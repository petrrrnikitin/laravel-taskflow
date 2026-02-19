<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentNotificationRepository implements NotificationRepositoryInterface
{
    public function forUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->notifications()->latest()->paginate($perPage);
    }

    public function findForUser(User $user, string $id): ?DatabaseNotification
    {
        return $user->notifications()->find($id);
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}