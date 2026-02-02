FROM php:8.2-apache

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite de Apache
RUN a2enmod rewrite

# Copiar código fuente
COPY src/www /var/www/html

# Ajustar permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
