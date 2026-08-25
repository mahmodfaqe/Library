#!/bin/sh
set -e

cd /var/www/html

# The database and storage live on volumes, so they start empty on a new host.
mkdir -p storage/framework/cache storage/framework/sessions \
         storage/framework/views storage/framework/pagecache \
         storage/logs storage/backups storage/app/books

if [ "${DB_CONNECTION}" = "sqlite" ] && [ ! -f database/database.sqlite ]; then
    echo "Creating a fresh SQLite database."
    touch database/database.sqlite
    chown www-data:www-data database/database.sqlite
fi

chown -R www-data:www-data storage
chmod -R u+rwX,g+rwX storage

if [ -z "${APP_KEY}" ] && ! grep -q '^APP_KEY=.\+' .env 2>/dev/null; then
    echo "APP_KEY is not set. Generate one with: php artisan key:generate --show" >&2
    exit 1
fi

# Compose waits for the database's own healthcheck, but the first connection
# can still land a moment early on a cold start.
if [ "${DB_CONNECTION}" = "mysql" ] || [ "${DB_CONNECTION}" = "mariadb" ]; then
    echo "Waiting for the database..."
    i=0
    until php artisan db:show --json >/dev/null 2>&1; do
        i=$((i + 1))
        if [ "$i" -ge 30 ]; then
            echo "The database did not become reachable in time." >&2
            exit 1
        fi
        sleep 2
    done
    echo "Database is up."
fi

php artisan migrate --force --no-interaction

# Compile config, routes and views into the image's cache for speed. Cleared
# first so a redeploy never serves the previous release's values.
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# A new release must not serve HTML rendered by the previous one, nor answers
# derived from it: the sitemap is held for twelve hours and search suggestions
# for ten minutes, so without this a release can take half a day to show.
# Sessions and queued jobs have their own tables and are untouched.
rm -f storage/framework/pagecache/*.html 2>/dev/null || true
php artisan cache:clear

echo "Library ready."

exec "$@"
