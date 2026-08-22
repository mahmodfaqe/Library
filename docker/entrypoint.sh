#!/bin/sh
set -e

cd /var/www/html

# The database and storage live on volumes, so they start empty on a new host.
mkdir -p storage/framework/cache storage/framework/sessions \
         storage/framework/views storage/framework/pagecache \
         storage/logs storage/backups database

if [ ! -f database/database.sqlite ]; then
    echo "Creating a fresh database."
    touch database/database.sqlite
fi

chown -R www-data:www-data storage database
chmod -R u+rwX,g+rwX storage database

if [ -z "${APP_KEY}" ] && ! grep -q '^APP_KEY=.\+' .env 2>/dev/null; then
    echo "APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
    exit 1
fi

php artisan migrate --force --no-interaction

# Compile config, routes and views into the image's cache for speed. Cleared
# first so a redeploy never serves the previous release's values.
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# A new release must not serve HTML rendered by the previous one.
rm -f storage/framework/pagecache/*.html 2>/dev/null || true

echo "Library ready."

exec "$@"
