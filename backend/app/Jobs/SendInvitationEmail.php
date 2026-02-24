<?php

namespace App\Jobs;

use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvitationEmail implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly Project $project,
        public readonly User $invitee,
    ) {
    }

    public function handle(): void
    {
        Mail::to($this->invitee->email)->send(new ProjectInvitationMail($this->project, $this->invitee));
    }
}
