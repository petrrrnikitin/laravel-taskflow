<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class TaskOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Task $task,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @return array<string, mixed> */
    public function toDatabase(object $notifiable): array
    {
        /** @var Carbon|null $dueDate */
        $dueDate = $this->task->due_date;

        return [
            'task_id' => $this->task->id,
            'task_title' => $this->task->title,
            'project_id' => $this->task->project_id,
            'due_date' => $dueDate?->toDateString(),
            'message' => "Task \"{$this->task->title}\" is overdue (was due {$dueDate?->toDateString()}).",
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        /** @var Carbon|null $dueDate */
        $dueDate = $this->task->due_date;

        return (new MailMessage())
            ->subject("Overdue task: {$this->task->title}")
            ->greeting('Hello!')
            ->line("Task \"{$this->task->title}\" is overdue.")
            ->line("It was due on {$dueDate?->toDateString()} and is still not completed.")
            ->line('Please update the task status or contact your project manager.')
            ->salutation('TaskFlow');
    }
}
