# syntax=docker/dockerfile:1

# ── 1. Front-end assets ───────────────────────────────────────────────
FROM node:22-alpine AS assets

WORKDIR /build
COPY package.json package-lock.json vite.config.js ./
RUN npm ci
COPY resources ./resources
# Tailwind scans the templates, so they have to be present at build time.
COPY app ./app
RUN npm run build

# ── 2. PHP dependencies ───────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /build
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-scripts \
        --no-autoloader \
        --prefer-dist \
        --no-interaction
COPY . .
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# ── 3. Runtime ────────────────────────────────────────────────────────
FROM php:8.3-fpm-alpine AS runtime

RUN apk add --no-cache nginx supervisor sqlite-libs \
    && apk add --no-cache --virtual .build-deps sqlite-dev $PHPIZE_DEPS \
    && docker-php-ext-install pdo_sqlite opcache \
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# Production PHP settings: opcache on, no source exposure.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.validate_timestamps=0'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'expose_php=Off'; \
        echo 'upload_max_filesize=16M'; \
        echo 'post_max_size=16M'; \
    } > /usr/local/etc/php/conf.d/zz-library.ini

WORKDIR /var/www/html

COPY --from=vendor  /build            /var/www/html
COPY --from=assets  /build/public/build /var/www/html/public/build

COPY docker/nginx.conf      /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh   /usr/local/bin/entrypoint

RUN chmod +x /usr/local/bin/entrypoint \
    && chown -R www-data:www-data /var/www/html \
    && rm -rf /var/www/html/tests /var/www/html/.github

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/up") !== false ? 0 : 1);'

ENTRYPOINT ["entrypoint"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
