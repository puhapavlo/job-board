FROM php:8.1-fpm

RUN apt-get update \
    && apt-get install -y \
        libicu-dev \
        libpq-dev \
        zip \
        unzip \
    && docker-php-ext-install \
        intl \
        pdo_pgsql \
        opcache \
    && pecl install \
        apcu \
    && docker-php-ext-enable \
        apcu \
        opcache

WORKDIR /var/www/html

CMD ["php-fpm"]

EXPOSE 9000
