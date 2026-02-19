<?php

namespace App\Services;

use App\DTO\Project\CreateProjectDTO;
use App\DTO\Project\UpdateProjectDTO;
use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Repositories\Contracts\ProjectRepositoryInterface;

class ProjectService
{
    public function __construct(
        private readonly ProjectRepositoryInterface $projects,
    ) {}

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
        $project->update(['status' => ProjectStatus::Archived]);

        return $project->fresh();
    }

    public function delete(Project $project): void
    {
        $this->projects->delete($project);
    }
}