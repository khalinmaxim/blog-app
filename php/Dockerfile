FROM php:8.1-fpm-alpine

RUN apk update && apk add --no-cache \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

WORKDIR /var/www/html

RUN chown -R www-data:www-data /var/www/html
