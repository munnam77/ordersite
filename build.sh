#!/usr/bin/env bash
# exit on error
set -o errexit

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Clear all caches
php artisan clear-compiled
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Create storage symbolic link
php artisan storage:link

# Migrate database (--force to run in production)
php artisan migrate --force

# Cache for optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache 