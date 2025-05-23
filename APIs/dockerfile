FROM php:8.3-fpm

# Installer les dépendances
RUN apt-get update && apt-get install -y unzip git zip libzip-dev && \
    docker-php-ext-install pdo pdo_mysql mysqli && \
    apt-get clean && rm -rf /var/lib/apt/lists/*


# Définir le répertoire de travail
WORKDIR /var/www/html

# Copier les fichiers du dossier code/ dans le conteneur
COPY code/ .

# Copier Composer depuis l’image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Exécuter composer install
RUN composer install --no-interaction --prefer-dist --no-dev

# Définir les permissions pour les fichiers photo
RUN mkdir -p /var/www/html/AirlockUnlock/bien/photos \
    && chown -R www-data:www-data /var/www/html/AirlockUnlock/bien/photos \
    && chmod -R 755 /var/www/html/AirlockUnlock/bien/photos

# Exposer le port PHP-FPM
EXPOSE 9000
