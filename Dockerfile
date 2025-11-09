FROM php:8.4-cli

RUN apt-get update && apt-get install -y \
    librdkafka-dev \
    libpq-dev \
    git \
    unzip \
    libzip-dev \
    && (pecl list | grep -q rdkafka || pecl install rdkafka) \
    && docker-php-ext-enable rdkafka \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

WORKDIR /var/www/html
COPY . /var/www/html