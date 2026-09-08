#!/bin/bash
set -e

echo "=========================================="
echo " Starting Automated Deployment for DukanHisab"
echo "=========================================="

echo "[1/4] Pulling latest code..."
git pull origin main || git pull

echo "[2/4] Running database migrations..."
php artisan migrate --force

echo "[3/4] Clearing and optimizing caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[4/4] Building assets..."
if command -v npm &> /dev/null; then
    npm run build
fi

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "=========================================="
echo " Deployment Successfully Completed!"
echo "=========================================="
