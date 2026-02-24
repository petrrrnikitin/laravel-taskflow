<?php

namespace App\Observers;

use App\Models\Project;
use App\Models\Task;
use App\Support\CacheKeys;
use Illuminate\Support\Facades\Cache;

class ProjectObserver
{
    public function created(Project $project): void
    {
        // Members not yet attached at this point — flush only owner's list
        Cache::store('redis')->tags(CacheKeys::userProjects($project->owner_id))->flush();
    }

    public function updated(Project $project): void
    {
        if ($project->wasChanged('owner_id')) {
            Cache::store('redis')->tags(CacheKeys::userProjects((int) $project->getOriginal('owner_id')))->flush();
        }

        $this->flushMemberProjectCaches($project);
    }

    public function deleting(Project $project): void
    {
        // Flush member caches BEFORE detach so we still have the relation
        $this->flushMemberProjectCaches($project);
        Cache::store('redis')->tags(CacheKeys::projectTasks($project->id))->flush();

        $project->tasks()->each(fn (Task $task) => $task->delete());
        $project->members()->detach();
    }

    /**
     * @param  array<int, mixed>  $ids
     * @param  array<string, mixed>  $attributes
     */
    public function pivotAttached(Project $project, string $relationName, array $ids, array $attributes): void
    {
        if ($relationName === 'members') {
            foreach ($ids as $userId) {
                Cache::store('redis')->tags(CacheKeys::userProjects((int) $userId))->flush();
            }
        }
    }

    /** @param array<int, mixed> $ids */
    public function pivotDetached(Project $project, string $relationName, array $ids): void
    {
        if ($relationName === 'members') {
            foreach ($ids as $userId) {
                Cache::store('redis')->tags(CacheKeys::userProjects((int) $userId))->flush();
            }
        }
    }

    private function flushMemberProjectCaches(Project $project): void
    {
        $project->loadMissing('members');

        $project->members
            ->pluck('id')
            ->push($project->owner_id)
            ->unique()
            ->each(fn (int $userId) => Cache::store('redis')->tags(CacheKeys::userProjects($userId))->flush());
    }
}
