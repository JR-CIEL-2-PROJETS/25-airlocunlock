#!/bin/bash
set -e

echo "💾 Sauvegarde des bases de données..."

BACKUP_DIR=$(pwd)/APIs/code

until docker exec mysql-container mysqladmin ping -h "localhost" -uroot -proot --silent; do
  echo "⏳ Attente que MySQL soit prêt..."
  sleep 3
done

echo "⏳ MySQL prêt, démarrage des dumps..."

docker exec mysql-container mysqldump -u root -proot airlockunlock > "$BACKUP_DIR/back_airlockunlock.sql"
docker exec mysql-container mysqldump -u root -proot Tapkey > "$BACKUP_DIR/back_tapkey.sql"

echo "✅ Bases sauvegardées dans $BACKUP_DIR"

echo "⏬ Arrêt des conteneurs API et Web..."
cd APIs && docker-compose down && cd ..
cd Web && docker-compose down && cd ..

echo "🧹 Nettoyage des fichiers non suivis"
git clean -fd

echo "📤 Ajout des modifications et push Git"
git add .
git commit -m "🚀 Backup et arrêt des services via docker-down.sh"
git push origin Deploiement

echo "✅ Fin du script docker-down.sh"
