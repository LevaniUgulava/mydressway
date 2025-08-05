<?php

namespace App\Providers;

use App\Models\Product;
use App\Observers\GlobalObserver;
use App\Observers\ProductLoggerObserver;
use App\Repository\Brand\BrandRepository;
use App\Repository\Brand\BrandRepositoryInterface;
use App\Repository\EligibleProduct\EligibleProductRepository;
use App\Repository\EligibleProduct\EligibleProductRepositoryInterface;
use App\Repository\Product\ProductRepository;
use App\Repository\Product\ProductRepositoryInterface;
use App\Repository\Promocode\PromocodeRepository;
use App\Repository\Promocode\PromocodeRepositoryInterface;
use App\Repository\Roles\RolesRepository;
use App\Repository\Roles\RolesRepositoryInterface;
use App\Repository\Search\SearchRepository;
use App\Repository\Search\SearchRepositoryInterface;
use App\Repository\UserStatus\UserStatusRepository;
use App\Repository\UserStatus\UserStatusRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->singleton(RolesRepositoryInterface::class, RolesRepository::class);
        $this->app->singleton(UserStatusRepositoryInterface::class, UserStatusRepository::class);
        $this->app->singleton(EligibleProductRepositoryInterface::class, EligibleProductRepository::class);
        $this->app->singleton(BrandRepositoryInterface::class, BrandRepository::class);
        $this->app->singleton(SearchRepositoryInterface::class, SearchRepository::class);
        $this->app->singleton(PromocodeRepositoryInterface::class, PromocodeRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {}
}
