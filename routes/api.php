<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Middleware\RefreshTokensMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public (Website / App)
|--------------------------------------------------------------------------
*/
Route::get('/site-settings', [SiteSettingController::class, 'show']);

Route::apiResource('brands', BrandController::class)->only(['index', 'show']);

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
Route::middleware([RefreshTokensMiddleware::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Protected (Admin Dashboard)
|--------------------------------------------------------------------------
*/
// Route::middleware([RefreshTokensMiddleware::class, 'role:admin'])->group(function () {
Route::middleware([RefreshTokensMiddleware::class])->group(function () {

    Route::post('/admin/site-settings', [SiteSettingController::class, 'storeOrUpdate']);
    Route::get('/admin/site-settings', [SiteSettingController::class, 'show']);
    Route::get('/admin/user', [AuthController::class, 'me']);

    Route::apiResource('admin/brands', BrandController::class);
});
