FROM php:8.3-apache

# Installer les extensions PDO et PDO_MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Activer les extensions PHP pour MySQL (au cas où elles ne sont pas activées par défaut)
RUN docker-php-ext-enable pdo_mysql

# Répertoire de travail
WORKDIR /var/www/html

# Exposer le port 80
EXPOSE 80
