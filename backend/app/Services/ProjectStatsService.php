<?php

namespace App\Services;

use App\DTO\Project\ProjectStats;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Repositories\Contracts\ProjectMemberRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;

readonly class ProjectStatsService
{
    public function __construct(
        private TaskRepositoryInterface $tasks,
        private ProjectMemberRepositoryInterface $members,
    ) {
    }

    public function getStats(Project $project): ProjectStats
    {
        $byStatus = collect([
            TaskStatus::Todo->value      => 0,
            TaskStatus::InProgress->value => 0,
            TaskStatus::Done->value      => 0,
        ])->merge($this->tasks->countByStatusForProject($project))->all();

        $byPriority = collect([
            TaskPriority::Low->value    => 0,
            TaskPriority::Medium->value => 0,
            TaskPriority::High->value   => 0,
        ])->merge($this->tasks->countByPriorityForProject($project))->all();

        $total = array_sum($byStatus);
        $done  = $byStatus[TaskStatus::Done->value];

        return new ProjectStats(
            totalTasks:      $total,
            byStatus:        $byStatus,
            byPriority:      $byPriority,
            overdueTasks:    $this->tasks->countOverdueForProject($project),
            completionRate:  $total > 0 ? round($done / $total * 100, 1) : 0.0,
            membersCount:    $this->members->countForProject($project),
            topAssignees:    $this->tasks->topAssigneesForProject($project, 5),
        );
    }
}
