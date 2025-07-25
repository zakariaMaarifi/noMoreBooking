FROM php:8.2-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git zip unzip wget libicu-dev libonig-dev libxml2-dev libzip-dev npm nodejs \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Symfony CLI (for symfony-cmd)
RUN wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony && \
    ln -s /usr/local/bin/symfony /usr/local/bin/symfony-cmd

# Set working directory to Apache root
WORKDIR /var/www/html

# Copy Symfony app from subfolder
COPY noMoreBook/ ./

# Fix Apache DocumentRoot to /public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf
# Install PHP dependencies (scripts enabled)
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets (ignore error if no package.json)
RUN if [ -f package.json ]; then npm install && npm run build; fi

# Set permissions for cache/logs/public
RUN mkdir -p var && chown -R www-data:www-data var public

# Clear and warmup cache (ignore error if .env not ready)
RUN php bin/console cache:clear --env=prod || true

EXPOSE 80

CMD ["apache2-foreground"]
