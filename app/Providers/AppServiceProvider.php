<?php

namespace App\Providers;

use App\Repositories\BannerRepository;
use App\Repositories\BrandRepository;
use App\Repositories\CartRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\DealRepository;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\DealRepositoryInterface;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use App\Repositories\Interfaces\ShippingMethodRepositoryInterface;
use App\Repositories\Interfaces\SiteSettingRepositoryInterface;
use App\Repositories\Interfaces\SubCategoryRepositoryInterface;
use App\Repositories\Interfaces\WishlistRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\ShippingMethodRepository;
use App\Repositories\SiteSettingRepository;
use App\Repositories\SubCategoryRepository;
use App\Repositories\WishlistRepository;
use Illuminate\Support\ServiceProvider;
use Biponix\SecureOtp\Services\SecureOtpService;
use Biponix\SecureOtp\Types\EmailType;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SiteSettingRepositoryInterface::class, SiteSettingRepository::class);
        $this->app->bind(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(SubCategoryRepositoryInterface::class, SubCategoryRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(BannerRepositoryInterface::class, BannerRepository::class);
        $this->app->bind(DealRepositoryInterface::class, DealRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, WishlistRepository::class);
        $this->app->bind(ShippingMethodRepositoryInterface::class, ShippingMethodRepository::class);

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        SecureOtpService::addType('email', new EmailType());

    }
}
