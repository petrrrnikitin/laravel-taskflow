<?php

namespace App\Support;

final class CacheKeys
{
    public const int TTL = 3600;

    public static function userProjects(int $userId): string
    {
        return "user:{$userId}:projects:".config('cache.versions.projects');
    }

    public static function projectTasks(int $projectId): string
    {
        return "project:{$projectId}:tasks:".config('cache.versions.tasks');
    }
}
