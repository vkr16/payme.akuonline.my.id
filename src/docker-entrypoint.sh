#!/bin/bash
set -e

echo "🚀 Bootstrapping PayMe Container..."

# Ensure SQLite DB file exists
if [ ! -f /var/www/database/database.sqlite ]; then
    echo "🗄️ Creating database.sqlite..."
    touch /var/www/database/database.sqlite
    chown www-data:www-data /var/www/database/database.sqlite
fi

# Storage symlink
if [ ! -L /var/www/public/storage ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link || true
fi

# Run migrations
echo "🗃️ Running migrations..."
php artisan migrate --force

# Cache optimization
echo "⚡ Caching Laravel configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database

echo "✅ PayMe Container is ready!"

exec "$@"
