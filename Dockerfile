FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install dependencies including rsync
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    rsync

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Start with a fresh Laravel installation to ensure we have the complete structure
RUN composer create-project --prefer-dist laravel/laravel:^10.0 /tmp/laravel && \
    cp -r /tmp/laravel/. /var/www/html/ && \
    rm -rf /tmp/laravel

# Copy our application files (special handling for nested structure)
COPY . .

# Check and handle the nested structure if it exists
RUN if [ -d "ordersite" ] && [ -f "ordersite/routes/web.php" ]; then \
    echo "Found nested structure, reorganizing..." && \
    cp -rf ordersite/* . && \
    rm -rf ordersite; \
fi

# Use the docker env file
RUN cp .env.docker .env

# Apply compatibility fixes BEFORE running composer install
RUN mkdir -p app/Providers app/Http/Middleware

# Create AppServiceProvider.php with compatibility fixes
RUN echo '<?php\n\
\n\
namespace App\\Providers;\n\
\n\
use Illuminate\\Support\\ServiceProvider;\n\
use Illuminate\\Support\\Facades\\URL;\n\
use Illuminate\\Http\\Request;\n\
\n\
class AppServiceProvider extends ServiceProvider\n\
{\n\
    /**\n\
     * Register any application services.\n\
     */\n\
    public function register(): void\n\
    {\n\
        //\n\
    }\n\
\n\
    /**\n\
     * Bootstrap any application services.\n\
     */\n\
    public function boot(): void\n\
    {\n\
        // For Render.com deployment, trust the proxies\n\
        // Using integer value directly to ensure compatibility with all Laravel versions\n\
        // This is equivalent to enabling all X-Forwarded-* headers\n\
        Request::setTrustedProxies(\n\
            [\"*\"],\n\
            0x7F  // This avoids using any constants for maximum compatibility\n\
        );\n\
\n\
        // Force HTTPS in production\n\
        if (env(\"FORCE_HTTPS\", false) || env(\"APP_ENV\") === \"production\") {\n\
            URL::forceScheme(\"https\");\n\
        }\n\
    }\n\
}' > app/Providers/AppServiceProvider.php

# Create TrustProxies.php with compatibility fixes
RUN echo '<?php\n\
\n\
namespace App\\Http\\Middleware;\n\
\n\
use Illuminate\\Http\\Middleware\\TrustProxies as Middleware;\n\
use Illuminate\\Http\\Request;\n\
\n\
class TrustProxies extends Middleware\n\
{\n\
    /**\n\
     * The trusted proxies for this application.\n\
     *\n\
     * @var array<int, string>|string|null\n\
     */\n\
    protected $proxies = \"*\";\n\
\n\
    /**\n\
     * The headers that should be used to detect proxies.\n\
     * Using direct hex value (0x7F = 127) to ensure compatibility with all Laravel versions\n\
     * This value enables all X-Forwarded-* headers\n\
     *\n\
     * @var int\n\
     */\n\
    protected $headers = 0x7F; // Equivalent to all X-Forwarded-* headers\n\
}' > app/Http/Middleware/TrustProxies.php

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html/storage -type d -exec chmod 775 {} \; && \
    find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;

# Install dependencies AFTER applying compatibility fixes
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs

# Configure Laravel, create links and clear caches
RUN php artisan clear-compiled && \
    php artisan storage:link && \
    php artisan route:clear && \
    php artisan config:clear && \
    php artisan view:clear && \
    php artisan cache:clear

# Make production-optimized build
RUN php artisan route:cache && \
    php artisan config:cache && \
    php artisan view:cache

# Configure Apache
RUN a2enmod rewrite && \
    sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 