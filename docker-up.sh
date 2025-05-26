#!/bin/bash

echo "🚀 Lancement des services API..."
cd APIs
docker-compose down
docker-compose build
docker-compose up -d
cd ..

echo "⏳ Attente que MySQL soit prêt..."
until docker exec mysql-container mysqladmin ping -h "localhost" -uroot -proot --silent; do
  printf '.'
  sleep 5
done
echo -e "\n✅ MySQL est prêt."

echo "📦 Importation des bases de données..."
docker exec -i mysql-container mysql -u root -proot airlockunlock < APIs/code/back_airlockunlock.sql
docker exec -i mysql-container mysql -u root -proot Tapkey < APIs/code/back_tapkey.sql
echo "✅ Bases importées."

echo "🔁 Réinstallation des dépendances PHP dans APIs/code..."
cd APIs/code
rm -rf vendor
composer install
cd ../..

echo "🌐 Lancement du frontend Web"
cd Web
docker-compose down
docker-compose build
docker-compose up -d
cd ..

echo "✅ Déploiement terminé avec succès."