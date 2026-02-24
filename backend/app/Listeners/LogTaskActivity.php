<?php

namespace App\Listeners;

use App\Enums\ActivityAction;
use App\Events\TaskAssigned;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Events\TaskUpdated;
use App\Repositories\Contracts\ActivityLogRepositoryInterface;

class LogTaskActivity
{
    public function __construct(
        private readonly ActivityLogRepositoryInterface $activityLogs,
    ) {}

    public function handle(TaskCreated|TaskUpdated|TaskStatusChanged|TaskAssigned $event): void
    {
        [$action, $properties] = match (true) {
            $event instanceof TaskCreated => [
                ActivityAction::Created,
                [],
            ],
            $event instanceof TaskUpdated => [
                ActivityAction::Updated,
                ['changes' => $event->changes],
            ],
            $event instanceof TaskStatusChanged => [
                ActivityAction::StatusChanged,
                [
                    'old_status' => $event->oldStatus->value,
                    'new_status' => $event->newStatus->value,
                ],
            ],
            $event instanceof TaskAssigned => [
                ActivityAction::Assigned,
                [
                    'assignee_id' => $event->assignee->id,
                    'assignee_name' => $event->assignee->name,
                ],
            ],
        };

        $this->activityLogs->log(
            taskId: $event->task->id,
            actorId: $event->actor->id,
            action: $action,
            properties: $properties,
        );
    }
}
