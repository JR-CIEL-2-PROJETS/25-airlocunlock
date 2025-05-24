#!/bin/bash

echo "💾 Sauvegarde des bases de données dans APIs/code/ ..."
docker exec mysql_container mysqldump -u root -pROOT_PASSWORD back_airlockunlock > APIs/code/back_airlockunlock.sql
docker exec mysql_container mysqldump -u root -pROOT_PASSWORD back_tapkey > APIs/code/back_tapkey.sql

echo "⬆️ Push Git"
git add .
git commit -m "sauvegarde des bases"
git push origin deploiement

echo "🧨 Arrêt des conteneurs..."
docker-compose down

echo "✅ Bases sauvegardées et conteneurs arrêtés."
