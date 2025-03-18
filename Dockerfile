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

# Set up a system that doesn't depend on composer files
# Create a fresh Laravel project regardless
RUN composer create-project --prefer-dist laravel/laravel:^10.0 /tmp/laravel && \
    cp -R /tmp/laravel/. /var/www/html/ && \
    rm -rf /tmp/laravel

# Now copy our actual application files (will overwrite the default Laravel files)
COPY . .

# Use the docker env file
RUN cp .env.docker .env

# Ensure correct directory structure with proper ownership
RUN mkdir -p storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    public

# Set ownership and permissions (critical for Laravel)
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html/storage -type d -exec chmod 775 {} \; && \
    find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;

# Install dependencies (ignoring platform requirements to handle any PHP version differences)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --ignore-platform-reqs

# Configure Laravel
RUN php artisan clear-compiled && \
    php artisan storage:link && \
    php artisan config:clear && \
    php artisan cache:clear

# Configure Apache
RUN a2enmod rewrite && \
    sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 