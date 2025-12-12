FROM php:8.0-fpm-alpine

# Workdir
WORKDIR /var/www/

RUN apk --update add wget \
        curl \
        build-base \
        libmemcached-dev \
        libmcrypt-dev \
        libxml2-dev \
        pcre-dev \
        zlib-dev \
        autoconf \
        cyrus-sasl-dev \
        libgsasl-dev \
        oniguruma-dev \
        openssl \
        openssl-dev \
        postgresql-dev \
        libzip-dev \
        libpng-dev \
        imagemagick \
        imagemagick-dev

RUN docker-php-ext-install pdo_pgsql
RUN docker-php-ext-install pcntl
RUN docker-php-ext-install zip
RUN docker-php-ext-install bz2
RUN docker-php-ext-install exif
RUN docker-php-ext-install gd

RUN pecl install imagick\
    &&  docker-php-ext-enable imagick

# artisan tinker busyBox fix
RUN apk add --update less

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install mc
RUN apk add mc && mkdir -p /.config/mc && mkdir -p /.cache/mc && mkdir -p /.local/share/mc
