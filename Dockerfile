FROM php:8.1-apache

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# First, create directories for a proper Laravel structure
RUN mkdir -p app bootstrap config database public resources routes storage tests vendor

# Copy our actual application files first (so the Laravel installer doesn't overwrite them)
COPY . .

# If the app isn't already a Laravel app, install Laravel to provide missing files
RUN if [ ! -f "artisan" ]; then \
    composer create-project --prefer-dist laravel/laravel:^10.0 /tmp/laravel && \
    # Copy only missing files, don't overwrite existing files
    rsync -av --ignore-existing /tmp/laravel/ /var/www/html/ && \
    rm -rf /tmp/laravel; \
fi

# Use the docker env file
RUN cp .env.docker .env

# Ensure correct directory structure with proper ownership
RUN mkdir -p storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

# Set ownership and permissions (critical for Laravel)
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html/storage -type d -exec chmod 775 {} \; && \
    find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;

# Install dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs

# Create any symbolic links and clear caches
RUN php artisan clear-compiled && \
    php artisan storage:link && \
    php artisan route:clear && \
    php artisan config:clear && \
    php artisan view:clear && \
    php artisan cache:clear

# Make a production-optimized build after all customizations
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