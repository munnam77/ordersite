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

# Create Laravel directory structure
RUN mkdir -p app bootstrap config database public resources routes storage tests vendor

# Copy our actual application files
COPY . .

# If artisan doesn't exist, install Laravel core files
RUN if [ ! -f "artisan" ]; then \
    echo "Installing Laravel core files..." && \
    # Create a fresh Laravel installation in a temporary directory
    composer create-project --prefer-dist laravel/laravel:^10.0 /tmp/laravel && \
    # Copy only important Laravel files that might be missing
    cp -n /tmp/laravel/artisan /var/www/html/ 2>/dev/null || true && \
    cp -n /tmp/laravel/package.json /var/www/html/ 2>/dev/null || true && \
    cp -n /tmp/laravel/webpack.mix.js /var/www/html/ 2>/dev/null || true && \
    # Copy directories structure but don't overwrite existing files
    mkdir -p bootstrap/cache && \
    mkdir -p public && \
    mkdir -p storage/app/public && \
    mkdir -p storage/framework/cache && \
    mkdir -p storage/framework/sessions && \
    mkdir -p storage/framework/testing && \
    mkdir -p storage/framework/views && \
    mkdir -p storage/logs && \
    # Remove the temporary Laravel project
    rm -rf /tmp/laravel; \
fi

# Use the docker env file
RUN cp .env.docker .env

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html/storage -type d -exec chmod 775 {} \; && \
    find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;

# Install dependencies
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