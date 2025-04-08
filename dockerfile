FROM php:8.3-apache

# Installer les extensions PDO et PDO_MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Activer les extensions PHP pour MySQL
RUN docker-php-ext-enable pdo_mysql

# Répertoire de travail
WORKDIR /var/www/html

# Set correct permissions on the 'photo-bien' directory
RUN mkdir -p /var/www/html/AirlockUnlock/bien/photo-bien \
    && chown -R www-data:www-data /var/www/html/AirlockUnlock/bien/photo-bien \
    && chmod -R 755 /var/www/html/AirlockUnlock/bien/photo-bien

# Exposer le port 80
EXPOSE 80
