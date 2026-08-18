<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('biteship-area-search', fn (Request $request): Limit => Limit::perMinute(12)->by($this->shippingLimitKey($request)));
        RateLimiter::for('biteship-rate-check', fn (Request $request): Limit => Limit::perMinute(6)->by($this->shippingLimitKey($request)));
        RateLimiter::for('biteship-shipping-selection', fn (Request $request): Limit => Limit::perMinute(20)->by($this->shippingLimitKey($request)));
    }

    private function shippingLimitKey(Request $request): string
    {
        return $request->user('customer')?->id ? 'customer:'.$request->user('customer')->id : 'ip:'.$request->ip();
    }
}
