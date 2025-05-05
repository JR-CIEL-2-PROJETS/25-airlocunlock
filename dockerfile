FROM php:8.3-fpm

# Install system dependencies required for Composer and PHP extensions
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    zip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean

# Installer Composer à partir de l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copier le projet dans l'image
COPY . .

# Installer automatiquement les dépendances PHP (comme firebase/php-jwt)
RUN composer install --no-interaction --prefer-dist --no-dev

# Définir les permissions pour les fichiers photo
RUN mkdir -p /var/www/html/AirlockUnlock/bien/photos \
    && chown -R www-data:www-data /var/www/html/AirlockUnlock/bien/photos \
    && chmod -R 755 /var/www/html/AirlockUnlock/bien/photos

# Exposer le port de PHP-FPM
EXPOSE 9000
