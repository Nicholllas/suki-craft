<?php

namespace App\Providers;

use App\Contracts\LocationProvider;
use App\Services\OpenRouteServiceLocationProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LocationProvider::class, OpenRouteServiceLocationProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('delivery-geocoding', fn (Request $request): array => [
            Limit::perMinute(90)->by('delivery-geocoding-global'),
            Limit::perMinute(20)->by('delivery-geocoding-customer:'.$request->user('customer')?->id),
        ]);

        RateLimiter::for('delivery-directions', fn (Request $request): array => [
            Limit::perMinute(35)->by('delivery-directions-global'),
            Limit::perMinute(10)->by('delivery-directions-customer:'.$request->user('customer')?->id),
        ]);
    }
}
