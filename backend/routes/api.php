<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskSearchController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('projects', ProjectController::class);
        Route::post('projects/{project}/archive', [ProjectController::class, 'archive']);

        Route::get('tasks/search', TaskSearchController::class);

        Route::prefix('projects/{project}')->middleware('project.member')->group(function () {
            Route::apiResource('tasks', TaskController::class)->except('index');
            Route::get('tasks', [TaskController::class, 'index']);
            Route::patch('tasks/{task}/status', [TaskController::class, 'changeStatus']);
            Route::get('tasks/{task}/activity', [ActivityController::class, 'taskActivity']);

            Route::get('tasks/{task}/comments', [CommentController::class, 'index']);
            Route::post('tasks/{task}/comments', [CommentController::class, 'store']);
            Route::patch('tasks/{task}/comments/{comment}', [CommentController::class, 'update']);
            Route::delete('tasks/{task}/comments/{comment}', [CommentController::class, 'destroy']);

            Route::get('members', [ProjectMemberController::class, 'index']);
            Route::post('members', [ProjectMemberController::class, 'store']);
            Route::delete('members/{user}', [ProjectMemberController::class, 'destroy']);
        });

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('read-all', [NotificationController::class, 'markAllAsRead']);
            Route::patch('{id}/read', [NotificationController::class, 'markAsRead']);
        });
    });
});