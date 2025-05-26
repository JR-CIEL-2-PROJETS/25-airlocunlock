#!/bin/bash

# Génération du certificat SSL systématique (toujours)
echo "🔐 Génération du certificat SSL..."
openssl req -new -newkey rsa:2048 -days 365 -nodes \
  -x509 -keyout Web/nginx/ssl/server.key -out Web/nginx/ssl/server.crt \
  -config Web/nginx/ssl/san.cnf
echo "✅ Certificat SSL généré avec succès."

# Récupération de l'adresse IP locale
if [[ "$OSTYPE" == "msys" || "$OSTYPE" == "win32" ]]; then
  API_HOST=$(ipconfig | grep -A 10 "Wireless" | grep "IPv4" | awk -F: '{print $2}' | xargs)
else
  API_HOST=$(hostname -I | awk '{print $1}')
fi

echo "📡 Adresse IP locale détectée : $API_HOST"

# Copier le certificat public pour que les clients puissent le télécharger
SSL_PUBLIC_PATH="Web/html/ssl"
mkdir -p $SSL_PUBLIC_PATH
cp APIs/ssl/server.crt $SSL_PUBLIC_PATH/
echo "📁 Certificat SSL public disponible sur : http://$API_HOST/ssl/server.crt"
echo "👉 Pensez à importer ce certificat dans votre navigateur pour éviter les alertes HTTPS."

# Lancement de l'API
echo "🚀 Lancement des services API..."
cd APIs
docker-compose down
docker-compose build
docker-compose up -d
cd ..

# Attente que MySQL soit prêt dans le conteneur mysql-container
echo "⏳ Attente que MySQL soit prêt..."
until docker exec mysql-container mysqladmin ping -h "localhost" -uroot -proot --silent; do
  printf '.'
  sleep 5
done
echo -e "\n✅ MySQL est prêt."

# 📦 Importation des bases SQL
echo "📦 Importation des bases de données..."
docker exec -i mysql-container mysql -u root -proot airlockunlock < APIs/code/back_airlockunlock.sql
docker exec -i mysql-container mysql -u root -proot Tapkey < APIs/code/back_tapkey.sql
echo "✅ Bases importées."

# Lancement du Web avec IP de l'API
echo "🌐 Lancement du frontend Web avec REACT_APP_API_URL=http://$API_HOST:8000"
cd Web
export REACT_APP_API_URL="http://$API_HOST:8000"
docker-compose down
docker-compose build
docker-compose up -d
cd ..

echo "✅ Déploiement terminé avec succès."