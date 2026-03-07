<?php

namespace App\Repositories\Eloquent;

use App\Enums\ProjectRole;
use App\Exceptions\MemberAlreadyExistsException;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectMemberRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\UniqueConstraintViolationException;

class EloquentProjectMemberRepository implements ProjectMemberRepositoryInterface
{
    public function allForProject(Project $project): Collection
    {
        return $project->members()->get();
    }

    public function add(Project $project, User $user, ProjectRole $role): void
    {
        try {
            $project->members()->attach($user->id, ['role' => $role->value]);
        } catch (UniqueConstraintViolationException) {
            throw new MemberAlreadyExistsException('User is already a member of this project.');
        }
    }

    public function remove(Project $project, User $user): void
    {
        $project->members()->detach($user->id);
    }

    public function isMember(Project $project, User $user): bool
    {
        return $project->members()->where('user_id', $user->id)->exists();
    }

    public function countForProject(Project $project): int
    {
        return $project->members()->count();
    }
}
