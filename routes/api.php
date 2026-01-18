<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Middleware\RefreshTokensMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public (Website / App)
|--------------------------------------------------------------------------
*/
Route::get('/site-settings', [SiteSettingController::class, 'show']);

Route::apiResource('brands', BrandController::class)->only(['index', 'show']);
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);

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
Route::middleware([RefreshTokensMiddleware::class])->prefix('admin')->group(function () {
    Route::post('site-settings', [SiteSettingController::class, 'storeOrUpdate']);
    Route::get('site-settings', [SiteSettingController::class, 'show']);

    Route::get('users', [AuthController::class, 'me']);
    Route::get('brands/active', [BrandController::class, 'activeBrands']);
    Route::apiResource('brands', BrandController::class);

    Route::get('categories/active', [CategoryController::class, 'activeCategories']);
    Route::apiResource('categories', CategoryController::class);

    Route::get('subcategories/active', [SubCategoryController::class, 'activeSubCategories']);
    Route::apiResource('subcategories', SubCategoryController::class);

    Route::get('products/active', [ProductController::class, 'activeProducts']);
    Route::apiResource('products', ProductController::class);

    Route::apiResource('banners', BannerController::class);
    Route::apiResource('deals', DealController::class);


});
