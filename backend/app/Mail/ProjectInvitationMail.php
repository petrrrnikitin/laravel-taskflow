<?php

namespace App\Mail;

use App\Models\Project;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Project $project,
        public readonly User $invitee,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "You've been added to: {$this->project->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.project-invitation',
        );
    }
}
