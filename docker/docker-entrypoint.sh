#!/bin/bash
set -e

# Ensure directories exist
mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
mkdir -p bootstrap/cache
mkdir -p /var/log/supervisor

# Remove any existing log files that might have wrong permissions
rm -f storage/logs/*.log

# Set ownership first - critical for all Laravel processes
chown -R www-data:www-data storage bootstrap/cache Modules resources app
chown -R www-data:www-data /var/log/supervisor

# Set proper permissions
chmod -R ug+rwX storage bootstrap/cache
chmod -R 775 storage/logs

# Laravel production optimizations (run as www-data to avoid creating root-owned files)
su -s /bin/bash www-data -c "php artisan optimize"

# Final permission check - ensure all files are owned by www-data
chown -R www-data:www-data storage bootstrap/cache
find storage -type f -exec chmod 664 {} \; 2>/dev/null || true
find storage -type d -exec chmod 775 {} \; 2>/dev/null || true

exec "$@"
