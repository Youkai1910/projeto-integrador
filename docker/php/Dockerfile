FROM php:8.2-fpm-alpine

# Compila a extensão PDO MySQL para conectar no container do banco
RUN docker-php-ext-install pdo pdo_mysql

# Copia o binário do Composer para gerenciar dependências dentro do container
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
