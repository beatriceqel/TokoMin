<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\AuthController;

// Endpoint Public Authentication
Route::post('/login', [AuthController::class, 'login']);

// Endpoint CRUD Books (Public/Bisa juga dimasukkan ke middleware auth jika diperlukan)
Route::apiResource('books', BookController::class);

// Endpoint Protected (Memerlukan Bearer Token via Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});