FROM php:8.2-fpm

# Instalar dependencias del sistema y extensiones PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar composer.json y composer.lock a la raíz del proyecto
WORKDIR /app
COPY composer.json composer.lock ./

# Instalar dependencias de PHP (genera /app/vendor)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copiar código fuente
COPY src/www /var/www/html

# Crear symlink de vendor para que config.php lo encuentre
RUN ln -s /app/vendor /vendor

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html

WORKDIR /var/www/html