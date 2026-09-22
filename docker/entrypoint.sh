#!/bin/sh
set -e

echo "==> Preparing Laravel application..."

# Ensure writable directories exist with correct permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink if not already linked
echo "==> Linking storage..."
php artisan storage:link --force || true

# Cache configs, routes, views for high performance in production
echo "==> Caching application configuration, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations and seed default credentials
echo "==> Running database migrations and seeders..."
php artisan migrate --force --seed

echo "==> Starting PHP-FPM and Nginx..."
php-fpm -D
exec nginx -g "daemon off;"
