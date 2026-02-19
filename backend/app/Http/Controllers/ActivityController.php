<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActivityLogResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\ActivityService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivityController extends Controller
{
    public function __construct(
        private readonly ActivityService $activityService,
    ) {}

    public function taskActivity(Project $project, Task $task): AnonymousResourceCollection
    {
        $this->authorize('view', $task);

        return ActivityLogResource::collection($this->activityService->getForTask($task));
    }
}