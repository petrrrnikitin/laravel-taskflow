<?php

namespace App\Providers;

use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Listeners\LogTaskActivity;
use App\Listeners\NotifyAssignee;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(TaskCreated::class, [LogTaskActivity::class, 'handle']);
        Event::listen(TaskStatusChanged::class, [LogTaskActivity::class, 'handle']);
        Event::listen(TaskAssigned::class, [NotifyAssignee::class, 'handle']);
    }
}