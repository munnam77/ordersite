<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

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
        // For Render.com deployment, trust the proxies
        // Using numeric value directly instead of constants for compatibility
        Request::setTrustedProxies(
            ['*'],
            Request::HEADER_X_FORWARDED_ALL
        );

        // Force HTTPS in production
        if (env('FORCE_HTTPS', false) || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }
} 