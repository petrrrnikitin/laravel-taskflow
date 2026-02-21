<?php

namespace App\Listeners;

use App\Events\ProjectMemberInvited;
use App\Jobs\SendInvitationEmail;

class NotifyInvitedMember
{
    public function handle(ProjectMemberInvited $event): void
    {
        SendInvitationEmail::dispatch($event->project, $event->invitee);
    }
}