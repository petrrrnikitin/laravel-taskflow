<?php

namespace App\Services;

use App\DTO\Project\CreateProjectDTO;
use App\DTO\Project\UpdateProjectDTO;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

readonly class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $projects,
    ) {}

    public function getForUser(User $user): Collection
    {
        return $this->projects->allForUser($user);
    }

    public function create(CreateProjectDTO $dto): Project
    {
        return $this->projects->create($dto);
    }

    public function update(Project $project, UpdateProjectDTO $dto): Project
    {
        return $this->projects->update($project, $dto);
    }

    public function archive(Project $project): Project
    {
        return $this->projects->archive($project);
    }

    public function delete(Project $project): void
    {
        $this->projects->delete($project);
    }
}
