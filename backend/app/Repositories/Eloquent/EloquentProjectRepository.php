<?php

namespace App\Repositories\Eloquent;

use App\DTO\Project\CreateProjectDTO;
use App\DTO\Project\UpdateProjectDTO;
use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\User;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class EloquentProjectRepository implements ProjectRepositoryInterface
{
    public function allForUser(User $user): Collection
    {
        return Project::query()
            ->where(function ($query) use ($user) {
                $query->where('owner_id', $user->id)
                    ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id));
            })
            ->with('owner')
            ->latest()
            ->get();
    }

    public function findById(int $id): ?Project
    {
        return Project::query()->with('owner', 'members')->find($id);
    }

    public function create(CreateProjectDTO $dto): Project
    {
        $project = Project::query()->create([
            'owner_id'    => $dto->ownerId,
            'name'        => $dto->name,
            'description' => $dto->description,
        ]);

        $project->members()->attach($dto->ownerId, ['role' => ProjectRole::Owner->value]);

        return $project->fresh();
    }

    public function update(Project $project, UpdateProjectDTO $dto): Project
    {
        $project->update([
            'name'        => $dto->name,
            'description' => $dto->description,
        ]);

        return $project->fresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}