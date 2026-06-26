FROM php:8.4-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

# 1. Install sistem dependensi
RUN apt-get update && apt-get install -y libpng-dev libzip-dev zip unzip git \
    && rm -rf /var/lib/apt/lists/*

# 2. Install ekstensi PHP
RUN docker-php-ext-install pdo_mysql gd zip sockets

# 3. Konfigurasi Apache
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 4. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

# 5. Copy composer files (layer caching)
COPY composer.json composer.lock ./

# 6. PENTING: Buat .env SEBELUM composer install
COPY .env.example .env

# 7. Install dependencies TANPA --optimize-autoloader dulu
RUN composer install --no-dev --no-interaction --no-scripts

# 8. Copy semua kode
COPY . .

# 9. Generate key dan optimize SETELAH semua file ada
RUN php artisan key:generate --force
RUN composer dump-autoload --optimize

# 10. Set permission
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80