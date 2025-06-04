#!/bin/bash

echo "🚀 Lancement des services API..."
cd APIs
docker-compose down
docker-compose build
docker-compose up -d
cd ..

echo "🔧 Correction des permissions du dossier photos..."
chmod -R 777 /home/airlockunlock/25-airlocunlock/APIs/code/AirlockUnlock/bien/photos
echo "✅ Permissions corrigées."

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
docker exec php-container rm -rf vendor
docker exec php-container composer install --no-dev --optimize-autoloader
echo "✅ Dépendances installées dans le conteneur."

echo "🌐 Lancement du frontend Web"
cd Web
docker-compose down
docker-compose build
docker-compose up -d
cd ..

echo "✅ Déploiement terminé avec succès."
