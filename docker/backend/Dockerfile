# Usamos la imagen oficial de PHP con Apache integrado
FROM php:8.2-apache

# Instalamos las extensiones para conectar con MySQL (mysqli y pdo)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activamos mod_rewrite por si usas rutas amigables en tu API
RUN a2enmod rewrite

# Copiamos tu código PHP al directorio público de Apache
COPY . /var/www/html/

# Ajustamos permisos para que Apache pueda leer los archivos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Apache expone el puerto 80 por defecto
EXPOSE 80