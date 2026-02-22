<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ReportPolicy
{
    public function create(User $user, Project $project): bool
    {
        return $project->isOwnedBy($user) || $project->hasMember($user);
    }

    public function view(User $user, Report $report): bool
    {
        $project = $report->project;

        if ($project === null) {
            throw new AuthorizationException('Report project not found.');
        }

        return $project->isOwnedBy($user) || $project->hasMember($user)
            ?: throw new AuthorizationException('You do not have access to this report.');
    }
}