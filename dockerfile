FROM php:8.3-fpm

# Install extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set the working directory
WORKDIR /var/www/html

# Set correct permissions on the 'photo-bien' directory
RUN mkdir -p /var/www/html/AirlockUnlock/bien/photos \
    && chown -R www-data:www-data /var/www/html/AirlockUnlock/bien/photos \
    && chmod -R 755 /var/www/html/AirlockUnlock/bien/photos

# Expose port 9000 for PHP-FPM
EXPOSE 9000