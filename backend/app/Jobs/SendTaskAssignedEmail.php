<?php

namespace App\Jobs;

use App\Mail\TaskAssignedMail;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskAssignedEmail implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly Task $task,
        public readonly User $assignee,
    ) {}

    public function handle(): void
    {
        Mail::to($this->assignee->email)->send(new TaskAssignedMail($this->task, $this->assignee));
    }
}
