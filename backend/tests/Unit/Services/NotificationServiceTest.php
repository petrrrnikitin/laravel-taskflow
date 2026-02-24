<?php

namespace Tests\Unit\Services;

use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    private NotificationRepositoryInterface&MockInterface $notificationRepo;

    private NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationRepo = $this->mock(NotificationRepositoryInterface::class);
        $this->service = app(NotificationService::class);
    }

    public function test_get_for_user_delegates_to_repository(): void
    {
        $user = new User;
        $paginator = new LengthAwarePaginator([], 0, 20);

        $this->notificationRepo->shouldReceive('forUser')->with($user, 20)->once()->andReturn($paginator);

        $result = $this->service->getForUser($user);

        $this->assertSame($paginator, $result);
    }

    public function test_get_for_user_passes_custom_per_page_to_repository(): void
    {
        $user = new User;
        $paginator = new LengthAwarePaginator([], 0, 5);

        $this->notificationRepo->shouldReceive('forUser')->with($user, 5)->once()->andReturn($paginator);

        $result = $this->service->getForUser($user, 5);

        $this->assertSame($paginator, $result);
    }

    public function test_mark_as_read_marks_notification_and_returns_it(): void
    {
        $user = new User;
        $notification = Mockery::mock(DatabaseNotification::class);
        $notification->shouldReceive('markAsRead')->once();

        $this->notificationRepo->shouldReceive('findForUser')->with($user, 'notif-uuid')->once()->andReturn($notification);

        $result = $this->service->markAsRead($user, 'notif-uuid');

        $this->assertSame($notification, $result);
    }

    public function test_mark_as_read_throws_model_not_found_when_notification_missing(): void
    {
        $user = new User;

        $this->notificationRepo->shouldReceive('findForUser')->with($user, 'unknown-uuid')->once()->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $this->service->markAsRead($user, 'unknown-uuid');
    }

    public function test_mark_all_as_read_delegates_to_repository(): void
    {
        $user = new User;

        $this->notificationRepo->shouldReceive('markAllAsRead')->with($user)->once();

        $this->service->markAllAsRead($user);
    }

    public function test_unread_count_delegates_to_repository(): void
    {
        $user = new User;

        $this->notificationRepo->shouldReceive('unreadCount')->with($user)->once()->andReturn(7);

        $result = $this->service->unreadCount($user);

        $this->assertSame(7, $result);
    }
}
