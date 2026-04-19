<?php

use App\Exceptions\AssigneeNotMemberException;
use App\Exceptions\CannotRemoveOwnerException;
use App\Exceptions\InviteeNotFoundException;
use App\Exceptions\MemberAlreadyExistsException;
use App\Exceptions\ProjectArchivedException;
use App\Exceptions\ReportNotReadyException;
use App\Http\Middleware\EnsureUserBelongsToProject;
use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'project.member' => EnsureUserBelongsToProject::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e): JsonResponse {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        });

        $exceptions->render(function (ModelNotFoundException $e): JsonResponse {
            $model = class_basename($e->getModel());

            return response()->json(['message' => "{$model} not found."], 404);
        });

        $exceptions->render(function (ValidationException $e): JsonResponse {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (InviteeNotFoundException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 404);
        });

        $exceptions->render(function (MemberAlreadyExistsException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 409);
        });

        $exceptions->render(function (CannotRemoveOwnerException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 422);
        });

        $exceptions->render(function (ReportNotReadyException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 409);
        });

        $exceptions->render(function (AssigneeNotMemberException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 422);
        });

        $exceptions->render(function (ProjectArchivedException $e): JsonResponse {
            return response()->json(['message' => $e->getMessage()], 409);
        });
    })->create();
