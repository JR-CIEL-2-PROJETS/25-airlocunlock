#!/bin/bash

echo "💾 Sauvegarde des bases de données dans le dossier code/..."
docker exec -i mysql-container mysqldump -u root -proot airlockunlock > code/back_airlockunlock.sql
docker exec -i mysql-container mysqldump -u root -proot Tapkey > code/back_tapkey.sql

echo "📦 Ajout, commit et push Git des fichiers SQL dans code/..."
git add .
git commit -m "save"
git push origin API-1

echo "🧹 Arrêt des containers..."
docker compose down

echo "✅ Bases sauvegardées dans, commit Git fait, containers arrêtés."
