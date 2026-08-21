#!/bin/bash

# Exit on error
set -e

echo "🚀 Starting PayMe Production Deployment..."

# 1. Pull latest changes (if git repo)
if [ -d ".git" ]; then
    echo "📥 Pulling latest git updates..."
    git pull origin main || true
fi

# 2. Install production Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader

# 3. Prepare SQLite database if DB_CONNECTION is sqlite and database does not exist
if [ -f ".env" ]; then
    if grep -q "DB_CONNECTION=sqlite" .env; then
        if [ ! -f "database/database.sqlite" ]; then
            echo "🗄️ Creating SQLite database..."
            touch database/database.sqlite
        fi
    fi
fi

# 4. Run database migrations
echo "🗃️ Running migrations..."
php artisan migrate --force

# 5. Create storage symlink if not present
if [ ! -L "public/storage" ]; then
    echo "🔗 Creating storage symlink..."
    php artisan storage:link
fi

# 6. Optimize Laravel Caches
echo "⚡ Optimizing Laravel caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Set correct permissions for storage and bootstrap cache
echo "🔒 Adjusting file permissions..."
sudo chown -R www-data:www-data storage bootstrap/cache database 2>/dev/null || true
sudo chmod -R 775 storage bootstrap/cache database 2>/dev/null || true

echo "✅ PayMe Deployment Completed Successfully!"
