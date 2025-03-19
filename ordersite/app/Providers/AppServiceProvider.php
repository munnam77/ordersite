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
        // Using integer value directly to ensure compatibility with all Laravel versions
        // This is equivalent to enabling all X-Forwarded-* headers
        Request::setTrustedProxies(
            ['*'],
            0x7F  // This avoids using any constants for maximum compatibility
        );

        // Force HTTPS in production
        if (env('FORCE_HTTPS', false) || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }
} 