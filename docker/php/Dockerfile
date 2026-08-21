FROM php:8.4.13RC1-fpm-alpine3.21

# Install system dependencies
RUN apk add bash git unzip curl icu-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev libxml2-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring bcmath xml zip intl gd \
    && docker-php-ext-enable opcache

RUN apk add ghostscript

COPY ./php.ini /usr/local/etc/php/php.ini