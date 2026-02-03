<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;

// Public Routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Routes (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Tasks Management
    Route::apiResource('tasks', TaskController::class);

    // File Uploads
    Route::post('/tasks/{id}/attachments', [TaskController::class, 'uploadAttachment']);
    Route::get('/attachments/{id}/download', [TaskController::class, 'downloadAttachment']);
    Route::delete('/attachments/{id}', [TaskController::class, 'deleteAttachment']);
});
