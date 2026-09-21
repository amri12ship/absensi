# syntax=docker/dockerfile:1

FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

FROM php:8.2-fpm AS runtime
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        ca-certificates \
        curl \
        git \
        unzip \
        libpq-dev \
        libzip-dev \
        libicu-dev \
        libpng-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pdo_mysql gd zip intl bcmath mbstring opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

RUN composer dump-autoload --no-dev --no-scripts --classmap-authoritative \
    && php artisan package:discover --ansi

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]