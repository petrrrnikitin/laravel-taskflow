<?php

namespace App\DTO\Project;

use Illuminate\Support\Collection;

final readonly class ProjectStats
{
    /**
     * @param  array<string, int>  $byStatus
     * @param  array<string, int>  $byPriority
     * @param  Collection<int, object{id:int,name:string,avatar:?string,closed_tasks_count:int}>  $topAssignees
     */
    public function __construct(
        public int $totalTasks,
        public array $byStatus,
        public array $byPriority,
        public int $overdueTasks,
        public float $completionRate,
        public int $membersCount,
        public Collection $topAssignees,
    ) {}
}
