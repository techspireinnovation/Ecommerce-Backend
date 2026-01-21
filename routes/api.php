<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SiteSettingController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\WishlistController;
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
Route::apiResource('subcategories', SubCategoryController::class)->only(['index', 'show']);
Route::get('products/active', [ProductController::class, 'activeProducts']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);
/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/refresh', [AuthController::class, 'refresh']);
/*
|--------------------------------------------------------------------------
| OTP – Email verification
|--------------------------------------------------------------------------
*/
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

/*
|--------------------------------------------------------------------------
| Forgot password (OTP based)
|--------------------------------------------------------------------------
*/
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendForgotPasswordOtp']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyForgotPasswordOtp']);
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword']);

Route::middleware([RefreshTokensMiddleware::class])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

/*
|--------------------------------------------------------------------------
| Protected (Admin Dashboard)
|--------------------------------------------------------------------------
*/
Route::middleware([RefreshTokensMiddleware::class, 'role:admin'])->prefix('admin')->group(function () {
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
    Route::get('products/{id}/seo', [ProductController::class, 'showForSeo']);
    Route::post('products/{id}/seo', [ProductController::class, 'storeSeo']);
    Route::put('products/{id}/seo', [ProductController::class, 'updateSeo']);  // update

    Route::apiResource('banners', BannerController::class);
    Route::apiResource('deals', DealController::class);


});

Route::middleware([RefreshTokensMiddleware::class, 'role:user'])->prefix('user')->group(function () {
    Route::apiResource('carts', CartController::class);
    Route::patch('carts/{id}/move-to-wish', [CartController::class, 'toggleMoveToWish']);
    Route::apiResource('wishlists', WishlistController::class);
    Route::patch('wishlists/{id}/move-to-cart', [WishlistController::class, 'toggleMoveToCart']);

});