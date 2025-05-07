<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $this->configureRateLimiting();
        });

        $this->routes(function () {
            Route::middleware('api')->prefix('api')->group(function () {
                require base_path('routes/api.php');
                require base_path('routes/Api/Auth.php');
                require base_path('routes/Api/Product.php');
                require base_path('routes/Api/Profile.php');
                require base_path('routes/Api/Admin/Category.php');
                require base_path('routes/Api/Admin/Roles.php');
                require base_path('routes/Api/Admin/UserStatus.php');
                require base_path('routes/Api/Admin/Banner.php');
                require base_path('routes/Api/Comment.php');
                require base_path('routes/Api/Cart.php');
                require base_path('routes/Api/Payment.php');
                require base_path('routes/Api/Order.php');
                require base_path('routes/Api/Collection.php');
            });



            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60);
        });
        RateLimiter::for('global', function ($request) {
            return Limit::perMinute(100);
        });
    }
}
