# Dockerfile

FROM php:8.2-apache

# Installe les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git zip unzip libicu-dev libonig-dev libxml2-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Active le mod_rewrite
RUN a2enmod rewrite

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copie l'application Symfony
WORKDIR /var/www/html
COPY . .

# Configuration Apache (redirection vers public/)
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Permissions (ajoute vendor et public pour éviter les erreurs)
RUN chown -R www-data:www-data /var/www/html/var /var/www/html/vendor /var/www/html/public || true

# Installe les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Cache warmup (optionnel, utile en prod)
RUN php bin/console cache:clear --env=prod || true

EXPOSE 80

# Lance Apache en mode premier plan
CMD ["apache2-foreground"]
