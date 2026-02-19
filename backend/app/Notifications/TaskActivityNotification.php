<?php

namespace App\Notifications;

use App\Events\TaskAssigned;
use App\Events\TaskStatusChanged;
use App\Events\TaskUpdated;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskActivityNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private Task   $task;
    private string $message;
    private string $subject;

    public function __construct(TaskStatusChanged|TaskUpdated|TaskAssigned $event)
    {
        $this->task = $event->task;

        [$this->subject, $this->message] = match (true) {
            $event instanceof TaskStatusChanged => [
                "Task status changed: {$event->task->title}",
                "The status of task \"{$event->task->title}\" was changed from \"{$event->oldStatus->value}\" to \"{$event->newStatus->value}\" by {$event->actor->name}.",
            ],
            $event instanceof TaskUpdated => [
                "Task updated: {$event->task->title}",
                "Task \"{$event->task->title}\" was updated by {$event->actor->name}.",
            ],
            $event instanceof TaskAssigned => [
                "You've been assigned to: {$event->task->title}",
                "You have been assigned to the task \"{$event->task->title}\" by {$event->actor->name}.",
            ],
        };
    }

    public function via(): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(): array
    {
        return [
            'task_id'    => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'message'    => $this->message,
        ];
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->greeting('Hello!')
            ->line($this->message)
            ->line('Log in to TaskFlow to view the full details.')
            ->salutation('TaskFlow');
    }
}