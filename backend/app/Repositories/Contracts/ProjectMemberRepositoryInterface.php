<?php

namespace App\Repositories\Contracts;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ProjectMemberRepositoryInterface
{
    /** @return Collection<int, User> */
    public function allForProject(Project $project): Collection;

    public function add(Project $project, User $user, ProjectRole $role): void;

    public function remove(Project $project, User $user): void;

    public function isMember(Project $project, User $user): bool;

    public function countForProject(Project $project): int;
}
