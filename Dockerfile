FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

RUN a2enmod rewrite

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY noMoreBook/ ./

# Debug: vérifier la présence de composer.json
RUN ls -l /var/www/html/composer.json
RUN cat /var/www/html/composer.json

# Installer les dépendances et vérifier autoload_runtime.php
RUN composer install --no-dev --optimize-autoloader
RUN ls -l /var/www/html/vendor/autoload_runtime.php

RUN php bin/console cache:clear --env=prod || true

EXPOSE 80

CMD ["apache2-foreground"]
