FROM php:8.2-fpm-alpine

# Compila a extensão PDO MySQL para conectar no container do banco
RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /var/www/html
