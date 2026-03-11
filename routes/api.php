<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ImageController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public routes (stricter throttle)
    Route::middleware('throttle:30,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected routes
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        // Notes — search BEFORE apiResource to avoid {note} capturing "search"
        Route::get('/notes/search', [NoteController::class, 'search']);
        Route::patch('/notes/{id}/restore', [NoteController::class, 'restore']);
        Route::delete('/notes/{id}/force', [NoteController::class, 'forceDelete']);

        Route::apiResource('notes', NoteController::class);

        // Tags
        // Images
        Route::post('/images', [ImageController::class, 'upload']);

        // Tags
        Route::get('/tags', [TagController::class, 'index']);
        Route::post('/tags', [TagController::class, 'store']);
        Route::delete('/tags/{id}', [TagController::class, 'destroy']);

        // Admin routes
        Route::middleware('admin')->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::put('/users/{id}', [UserController::class, 'update']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);
        });

    });

});
