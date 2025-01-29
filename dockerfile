# Utiliser l'image Nginx officielle comme base
FROM nginx:alpine

# Copier le fichier de configuration Nginx personnalisé dans le conteneur
COPY nginx.conf /etc/nginx/nginx.conf

# Copier les fichiers du projet dans le conteneur
COPY . /usr/share/nginx/html

# Exposer le port 80 pour l'accès HTTP
EXPOSE 80
