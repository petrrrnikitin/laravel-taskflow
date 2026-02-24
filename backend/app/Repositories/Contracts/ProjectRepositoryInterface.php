<?php

namespace App\Repositories\Contracts;

use App\DTO\Project\CreateProjectDTO;
use App\DTO\Project\UpdateProjectDTO;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    /** @return Collection<int, Project> */
    public function allForUser(User $user): Collection;

    public function findById(int $id): ?Project;

    public function create(CreateProjectDTO $dto): Project;

    public function update(Project $project, UpdateProjectDTO $dto): Project;

    public function archive(Project $project): Project;

    public function delete(Project $project): void;
}
