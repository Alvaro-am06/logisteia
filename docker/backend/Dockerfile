FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar archivos de composer y código fuente
COPY composer.json composer.lock ./
COPY src/www ./

# Instalar dependencias de PHP (Optimizado para producción)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html