<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
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

        Route::prefix('projects/{project}')->group(function () {
            Route::apiResource('tasks', TaskController::class)->except('index');
            Route::get('tasks', [TaskController::class, 'index']);
            Route::patch('tasks/{task}/status', [TaskController::class, 'changeStatus']);
        });
    });
});