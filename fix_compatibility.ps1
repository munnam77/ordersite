# Create directories if they don't exist
New-Item -Path "app\Providers" -ItemType Directory -Force | Out-Null
New-Item -Path "app\Http\Middleware" -ItemType Directory -Force | Out-Null

# Create AppServiceProvider.php
$appServiceProvider = @'
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
'@

# Create TrustProxies.php
$trustProxies = @'
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
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     * Using direct hex value (0x7F = 127) to ensure compatibility with all Laravel versions
     * This value enables all X-Forwarded-* headers
     *
     * @var int
     */
    protected $headers = 0x7F; // Equivalent to all X-Forwarded-* headers
}
'@

# Write the files
Set-Content -Path "app\Providers\AppServiceProvider.php" -Value $appServiceProvider
Set-Content -Path "app\Http\Middleware\TrustProxies.php" -Value $trustProxies

Write-Output "Compatibility fixes applied successfully." 