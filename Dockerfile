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
    rsync \
    libssl-dev \
    ssl-cert

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Enable Apache modules
RUN a2enmod rewrite headers ssl

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

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html/storage -type d -exec chmod 775 {} \; && \
    find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;

# Create storage directory for logs
RUN mkdir -p /var/www/html/storage/logs && \
    touch /var/www/html/storage/logs/laravel.log && \
    chmod -R 775 /var/www/html/storage/logs

# Install dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs

# Configure Laravel, create links and clear caches but don't cache routes yet
RUN php artisan clear-compiled && \
    php artisan storage:link && \
    php artisan route:clear && \
    php artisan config:clear && \
    php artisan view:clear && \
    php artisan cache:clear

# Add custom AppServiceProvider that forces HTTPS in production
RUN echo "<?php \
    namespace App\\Providers; \
    use Illuminate\\Support\\Facades\\URL; \
    use Illuminate\\Support\\ServiceProvider; \
    class AppServiceProvider extends ServiceProvider { \
        public function register() {} \
        public function boot() { \
            if (env('FORCE_HTTPS', false)) { \
                URL::forceScheme('https'); \
            } \
        } \
    }" > /var/www/html/app/Providers/AppServiceProvider.php

# Configure Apache
RUN sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf && \
    echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/sites-available/000-default.conf && \
    echo '<IfModule mod_headers.c>\n\
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"\n\
    Header always set X-Content-Type-Options "nosniff"\n\
    Header always set X-XSS-Protection "1; mode=block"\n\
    Header always set X-Frame-Options "SAMEORIGIN"\n\
</IfModule>' >> /etc/apache2/sites-available/000-default.conf

# Now make production-optimized build
RUN php artisan config:cache

# Create file to enable error reporting
RUN echo "<?php \
    ini_set('display_errors', 1); \
    ini_set('display_startup_errors', 1); \
    error_reporting(E_ALL);" > /var/www/html/public/error_reporting.php && \
    echo "<?php require_once('error_reporting.php'); require_once('index.php');" > /var/www/html/public/debug.php

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 