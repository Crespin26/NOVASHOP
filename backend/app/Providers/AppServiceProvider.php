<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('forms', fn (Request $request): Limit => Limit::perMinute(6)->by($request->ip()));
        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)->by(hash('sha256', strtolower((string) $request->input('email')).'|'.$request->ip())));
        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(120)
            ->by($request->user()?->id ?: $request->ip()));
    }
}
