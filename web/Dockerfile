FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libzip-dev zip unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# COPIAMOS php.ini A LA RUTA CORRECTA
COPY php.ini /usr/local/etc/php/conf.d/php.ini

# Copiamos el código fuente
COPY . /var/www/html

EXPOSE 80
