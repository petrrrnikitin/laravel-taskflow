<?php

namespace App\Services;

use App\DTO\Project\ProjectStats;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Carbon;

readonly class ProjectStatsService
{
    public function getStats(Project $project): ProjectStats
    {
        $byStatus = Task::query()
            ->where('project_id', $project->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->map(fn($c) => (int)$c);

        $byPriority = Task::query()
            ->where('project_id', $project->id)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->map(fn($c) => (int)$c);

        $byStatus = collect([
            TaskStatus::Todo->value => 0,
            TaskStatus::InProgress->value => 0,
            TaskStatus::Done->value => 0,
        ])->merge($byStatus)->all();

        $byPriority = collect([
            TaskPriority::Low->value => 0,
            TaskPriority::Medium->value => 0,
            TaskPriority::High->value => 0,
        ])->merge($byPriority)->all();

        $total = array_sum($byStatus);
        $done = $byStatus[TaskStatus::Done->value];

        $overdueTasks = Task::query()
            ->where('project_id', $project->id)
            ->whereNotNull('due_date')
            ->where('due_date', '<', Carbon::today())
            ->whereNot('status', TaskStatus::Done)
            ->count();

        $topAssignees = Task::query()
            ->join('users', 'tasks.assignee_id', '=', 'users.id')
            ->where('tasks.project_id', $project->id)
            ->where('tasks.status', TaskStatus::Done->value)
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->selectRaw('users.id, users.name, users.avatar, COUNT(*) as closed_tasks_count')
            ->orderByDesc('closed_tasks_count')
            ->limit(5)
            ->get();

        return new ProjectStats(
            totalTasks: $total,
            byStatus: $byStatus,
            byPriority: $byPriority,
            overdueTasks: $overdueTasks,
            completionRate: $total > 0 ? round($done / $total * 100, 1) : 0.0,
            membersCount: $project->members()->count(),
            topAssignees: $topAssignees,
        );
    }
}
