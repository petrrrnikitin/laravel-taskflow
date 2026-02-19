<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        return $project->isOwnedBy($user) || $project->hasMember($user);
    }

    public function update(User $user, Project $project): bool
    {
        return $project->isOwnedBy($user)
            ?: throw new AuthorizationException('Only the project owner can edit it.');
    }

    public function delete(User $user, Project $project): bool
    {
        return $project->isOwnedBy($user)
            ?: throw new AuthorizationException('Only the project owner can delete it.');
    }
}
