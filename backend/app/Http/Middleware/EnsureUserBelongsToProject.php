<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserBelongsToProject
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Project|null $project */
        $project = $request->route('project');

        if (!$project instanceof Project) {
            return $next($request);
        }

        $user = $request->user();

        if (!$user instanceof User) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (!$project->isOwnedBy($user) && !$project->hasMember($user)) {
            return response()->json(['message' => 'You are not a member of this project.'], 403);
        }

        return $next($request);
    }
}