#!/bin/bash
set -e

# Fix permissions
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache
chmod -R 775 storage/logs

# Laravel production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec "$@"
