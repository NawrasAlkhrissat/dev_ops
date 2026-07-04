FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

COPY webpage/ /var/www/html/
COPY css/ /var/www/html/css/

EXPOSE 80