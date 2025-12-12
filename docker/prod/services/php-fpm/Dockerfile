FROM php:8.0.24-fpm

ENV TZ=Europe/Moscow
ENV PUPPETEER_CACHE_DIR=/root/.cache/puppeteer
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update \
    && apt-get install -y gnupg gosu curl ca-certificates zip unzip git supervisor libcap2-bin libpng-dev python2 \
    && mkdir -p ~/.gnupg \
    && chmod 600 ~/.gnupg \
    && echo "disable-ipv6" >> ~/.gnupg/dirmngr.conf \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys E5267A6C \
    && apt-key adv --homedir ~/.gnupg --keyserver hkp://keyserver.ubuntu.com:80 --recv-keys C300EE8C

RUN apt-get update \
    && apt-get install -y -qq --no-install-recommends \
    apt-utils \
    libpq-dev \
    libmcrypt-dev \
    libxml2-dev \
    libgd-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libjpeg-dev \
    libzip-dev \
    libbz2-dev \
    mcrypt \
    unzip \
    git \
    zip

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    opcache \
    bcmath \
    ctype \
    iconv \
    exif \
    intl \
    xml \
    zip \
    gd \
    bz2


RUN php -r "readfile('http://getcomposer.org/installer');" | php -- --install-dir=/usr/bin/ --filename=composer \
    && apt-get install -y postgresql-client \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN ln -s /usr/local/bin/php /usr/bin/php

WORKDIR /var/www
