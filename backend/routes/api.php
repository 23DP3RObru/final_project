<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;

// Test route
Route::get('/test', function () {
    return response()->json([
        'message' => 'API is working!',
        'timestamp' => now()->toDateTimeString(),
    ]);
});

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toDateTimeString(),
    ]);
});

// Authentication routes (public)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/auth/me', [AuthController::class, 'me']);

// User management routes (admin only)
Route::middleware('check.admin')->prefix('admin')->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::put('/users/{id}/promote', [UserController::class, 'promoteToAdmin']);
    Route::put('/users/{id}/demote', [UserController::class, 'demoteFromAdmin']);
});

// Items API routes
Route::get('/items', [ItemController::class, 'index']); // Public: Everyone can view items
Route::get('/items/{item}', [ItemController::class, 'show']); // Public: Everyone can view item details

// Protected item management routes
Route::middleware('check.auth')->group(function () {
    Route::post('/items', [ItemController::class, 'store']); // Create item (must be authenticated)
    Route::put('/items/{item}', [ItemController::class, 'update']); // Update item (must be owner or admin)
    Route::delete('/items/{item}', [ItemController::class, 'destroy']); // Delete item (must be owner or admin)
});