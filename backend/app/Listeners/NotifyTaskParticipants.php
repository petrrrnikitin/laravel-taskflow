<?php

namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Events\TaskStatusChanged;
use App\Events\TaskUpdated;
use App\Models\User;
use App\Notifications\TaskActivityNotification;
use Illuminate\Support\Collection;

class NotifyTaskParticipants
{
    public function handle(TaskStatusChanged|TaskUpdated|TaskAssigned $event): void
    {
        $task  = $event->task;
        $actor = $event->actor;

        $recipients = collect()
            ->push($task->creator ?? User::find($task->creator_id))
            ->push($task->assignee)
            ->filter()
            ->unique('id')
            ->reject(fn(User $u) => $u->id === $actor->id);

        $notification = new TaskActivityNotification($event);

        $recipients->each(fn(User $user) => $user->notify($notification));
    }
}