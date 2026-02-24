<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;

readonly class NotificationService
{
    public function __construct(
        private NotificationRepositoryInterface $notifications,
    ) {
    }

    /** @return LengthAwarePaginator<int, DatabaseNotification> */
    public function getForUser(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $this->notifications->forUser($user, $perPage);
    }

    public function markAsRead(User $user, string $id): DatabaseNotification
    {
        $notification = $this->notifications->findForUser($user, $id);

        if (! $notification) {
            throw (new ModelNotFoundException())->setModel(DatabaseNotification::class);
        }

        $notification->markAsRead();

        return $notification;
    }

    public function markAllAsRead(User $user): void
    {
        $this->notifications->markAllAsRead($user);
    }

    public function unreadCount(User $user): int
    {
        return $this->notifications->unreadCount($user);
    }
}
