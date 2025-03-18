#!/usr/bin/env bash
# exit on error
set -o errexit

composer install --optimize-autoloader --no-dev
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache 