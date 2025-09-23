<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/google-signin', [AuthController::class, 'googleSignIn']);

Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/users/pending', [UserController::class, 'getPendingUsers']);
        Route::patch('/users/{id}/approve', [UserController::class, 'approve']);
        Route::patch('/users/{id}/reject', [UserController::class, 'reject']);
    });
});


