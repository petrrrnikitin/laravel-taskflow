<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'task_id'    => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'due_date'   => $this->task->due_date?->toDateString(),
            'message'    => "Task \"{$this->task->title}\" is overdue (was due {$this->task->due_date?->toDateString()}).",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Overdue task: {$this->task->title}")
            ->greeting('Hello!')
            ->line("Task \"{$this->task->title}\" is overdue.")
            ->line("It was due on {$this->task->due_date?->toDateString()} and is still not completed.")
            ->line('Please update the task status or contact your project manager.')
            ->salutation('TaskFlow');
    }
}