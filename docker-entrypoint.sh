#!/bin/bash
set -e

# Generate key if not set
php artisan key:generate --force --no-interaction 2>/dev/null || true

# Cache config and routes for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Update Apache to listen on Render's PORT (default 10000)
sed -i "s/Listen 80/Listen ${PORT:-10000}/" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT:-10000}/" /etc/apache2/sites-available/000-default.conf

# Start Apache
apache2-foreground
