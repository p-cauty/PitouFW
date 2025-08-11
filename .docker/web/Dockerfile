FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y curl libcurl4-openssl-dev zlib1g-dev libpng-dev libc-client-dev libkrb5-dev libonig-dev libedit-dev libxml2-dev libzip-dev

RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl
RUN docker-php-ext-install curl gd imap mbstring pdo_mysql xml zip

RUN apt-get install -y redis-tools

RUN a2enmod rewrite

RUN chown -R www-data:www-data /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
