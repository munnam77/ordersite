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

# Copy application files
COPY . .

# Use the docker env file
RUN cp .env.docker .env

# Create Laravel directories if they don't exist
RUN mkdir -p storage/app/public && \
    mkdir -p storage/framework/cache && \
    mkdir -p storage/framework/sessions && \
    mkdir -p storage/framework/testing && \
    mkdir -p storage/framework/views && \
    mkdir -p storage/logs && \
    mkdir -p bootstrap/cache && \
    mkdir -p public

# Create a default index.php if it doesn't exist
RUN if [ ! -f public/index.php ]; then \
    echo '<?php echo "Laravel application is being set up..."; ?>' > public/index.php; \
    fi

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && chmod -R 755 /var/www/html/public

# Install dependencies
RUN composer install --optimize-autoloader --no-dev

# Clear cache and setup
RUN php artisan clear-compiled \
    && php artisan storage:link

# Configure Apache
RUN a2enmod rewrite
RUN sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www\/html\/public/g' /etc/apache2/sites-available/000-default.conf

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"] 