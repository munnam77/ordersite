#!/bin/bash

# Create directories if they don't exist
mkdir -p app/Providers
mkdir -p app/Http/Middleware

# Create AppServiceProvider.php
cat > app/Providers/AppServiceProvider.php << EOL
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
EOL

# Create TrustProxies.php
cat > app/Http/Middleware/TrustProxies.php << EOL
<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string>|string|null
     */
    protected \$proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     * Using direct hex value (0x7F = 127) to ensure compatibility with all Laravel versions
     * This value enables all X-Forwarded-* headers
     *
     * @var int
     */
    protected \$headers = 0x7F; // Equivalent to all X-Forwarded-* headers
}
EOL

echo "Compatibility fixes applied successfully."
