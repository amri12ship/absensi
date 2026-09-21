#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/app/public/selfies storage/app/private \
    storage/framework/cache/data storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
    php artisan key:generate --force --ansi
fi

php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan migrate --force

php-fpm -D
exec nginx -g 'daemon off;'