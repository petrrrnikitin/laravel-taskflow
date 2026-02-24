<?php

namespace App\Services;

use App\DTO\Project\CreateProjectDTO;
use App\DTO\Project\UpdateProjectDTO;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Support\CacheKeys;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

readonly class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $projects,
    ) {
    }

    /** @return Collection<int, Project> */
    public function getForUser(User $user): Collection
    {
        return Cache::store('redis')->tags(CacheKeys::userProjects($user->id))
            ->remember(
                CacheKeys::userProjects($user->id),
                CacheKeys::TTL,
                fn () => $this->projects->allForUser($user),
            );
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
