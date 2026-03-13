FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    && docker-php-ext-install \
        pdo_mysql \
        intl \
        zip \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY conf.d/app.ini /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html