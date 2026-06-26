FROM php:8.4-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

# 1. Install sistem dependensi
RUN apt-get update && apt-get install -y libpng-dev libzip-dev zip unzip git \
    && rm -rf /var/lib/apt/lists/*

# 2. Install ekstensi PHP
RUN docker-php-ext-install pdo_mysql gd zip sockets

# 3. Konfigurasi Apache (Rewrite + DocumentRoot + ServerName)
RUN a2enmod rewrite
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
# Perbaikan error "Could not reliably determine server's fully qualified domain name"
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# 4. Instalasi Composer (Strategi Caching)
# Kita copy composer.json/lock dulu agar docker bisa melakukan cache layer
# sehingga proses build berikutnya lebih cepat jika tidak ada perubahan package
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 5. Salin sisa kode aplikasi dan atur permission
COPY . .
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80