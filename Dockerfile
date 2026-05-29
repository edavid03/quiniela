FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-req=ext-gd

FROM php:8.3-fpm-alpine AS app

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl \
        freetype-dev \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        mariadb-client \
        oniguruma-dev \
        shadow \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        zip \
    && addgroup -g 1000 laravel \
    && adduser -D -G laravel -u 1000 laravel

COPY --chown=laravel:laravel . .
COPY --from=vendor --chown=laravel:laravel /app/vendor ./vendor
COPY --from=assets --chown=laravel:laravel /app/public/build ./public/build
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/entrypoint.sh /usr/local/bin/laravel-entrypoint

RUN chmod +x /usr/local/bin/laravel-entrypoint \
    && mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && php artisan package:discover --ansi \
    && chown -R laravel:laravel storage bootstrap/cache

USER laravel

EXPOSE 9000

ENTRYPOINT ["laravel-entrypoint"]
CMD ["php-fpm"]

FROM nginx:1.27-alpine AS nginx

WORKDIR /var/www/html

COPY --from=app /var/www/html/public ./public
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir -p storage/app/public \
    && ln -sf /var/www/html/storage/app/public /var/www/html/public/storage
