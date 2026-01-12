<?php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// -------- Public routes (no token required) --------
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);

// -------- Protected routes (token + auto-refresh) --------
Route::middleware([\App\Http\Middleware\RefreshTokensMiddleware::class])->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
