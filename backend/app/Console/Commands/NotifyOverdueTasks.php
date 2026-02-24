<?php

namespace App\Console\Commands;

use App\Enums\TaskStatus;
use App\Models\Task;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class NotifyOverdueTasks extends Command
{
    protected $signature = 'tasks:notify-overdue';

    protected $description = 'Send overdue notifications to assignees of tasks past their due date';

    public function handle(): int
    {
        $count = 0;
        $today = Carbon::today();

        Task::query()
            ->whereNotNull('assignee_id')
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->whereNot('status', TaskStatus::Done)
            ->where(function ($q) use ($today): void {
                $q->whereNull('last_overdue_notified_at')
                    ->orWhere('last_overdue_notified_at', '<', $today);
            })
            ->with('assignee')
            ->chunkById(100, function ($tasks) use (&$count): void {
                foreach ($tasks as $task) {
                    if ($task->assignee === null) {
                        continue;
                    }

                    $task->assignee->notify(new TaskOverdueNotification($task));

                    Task::whereKey($task->id)->update(['last_overdue_notified_at' => Carbon::now()]);

                    $count++;
                }
            });

        $this->info("Sent overdue notifications for {$count} task(s).");

        return self::SUCCESS;
    }
}
