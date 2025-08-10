<?php

namespace App\Providers;

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
        // Only force HTTPS if we're in production AND the request is actually secure
        // This prevents redirect loops when behind proxies
        if ($this->app->environment('production') && request()->secure()) {
            \URL::forceScheme('https');
        }
    }
}
