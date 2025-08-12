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
        // Force HTTPS for URL generation only (not requests)
        // This ensures assets are loaded over HTTPS
        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }
    }
}
