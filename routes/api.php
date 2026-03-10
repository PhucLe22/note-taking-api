<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NoteController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public routes (stricter throttle)
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
    });

    // Protected routes
    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        // Notes — search BEFORE apiResource to avoid {note} capturing "search"
        Route::get('/notes/search', [NoteController::class, 'search']);
        Route::patch('/notes/{id}/restore', [NoteController::class, 'restore']);

        Route::apiResource('notes', NoteController::class);

        // Tags
        Route::get('/tags', [TagController::class, 'index']);
        Route::post('/tags', [TagController::class, 'store']);
        Route::delete('/tags/{id}', [TagController::class, 'destroy']);

    });

});
