FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

# COPIAMOS php.ini A LA RUTA CORRECTA
COPY php.ini /usr/local/etc/php/conf.d/php.ini

# Copiamos el código fuente
COPY . /var/www/html

EXPOSE 80
