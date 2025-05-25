#!/bin/bash
echo "💾 Sauvegarde des bases de données..."

docker exec mysql-container mysqldump -u root -proot airlockunlock > APIs/code/back_airlockunlock.sql
docker exec mysql-container mysqldump -u root -proot Tapkey > APIs/code/back_tapkey.sql

echo "✅ Bases sauvegardées dans APIs/code/"


echo "⏬ Arrêt des conteneurs API et Web..."

# Arrêt API
cd APIs
docker-compose down
cd ..

# Arrêt Web
cd Web
docker-compose down
cd ..




echo "⬆️ Push Git"

git add APIs/code/back_airlockunlock.sql APIs/code/back_tapkey.sql
git commit -m "Sauvegarde des bases et arrêt des conteneurs"
git push origin Deploiement

echo "✅ Fin du script docker-down.sh"
