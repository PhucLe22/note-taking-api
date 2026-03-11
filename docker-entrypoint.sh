#!/bin/bash
set -e

# Ensure storage directories exist and are writable
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/framework/{cache,sessions,views}
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Do NOT cache config — let env vars be read at runtime
php artisan config:clear
php artisan route:cache
php artisan view:cache

# Run migrations and seed if users table is empty
php artisan migrate --force
php artisan db:seed --force 2>/dev/null || true

# Update Apache to listen on Render's PORT (default 10000)
sed -i "s/Listen 80/Listen ${PORT:-10000}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT:-10000}/" /etc/apache2/sites-available/000-default.conf

# Start Apache
apache2-foreground
