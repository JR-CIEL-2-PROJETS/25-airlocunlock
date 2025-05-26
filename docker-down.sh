#!/bin/bash
set -e  # Arrêter le script si une commande échoue

echo "💾 Sauvegarde des bases de données..."

BACKUP_DIR=$(pwd)/APIs/code

# Vérifier que MySQL répond avant de faire la sauvegarde
until docker exec mysql-container mysqladmin ping -h "localhost" -uroot -proot --silent; do
  echo "⏳ Attente que MySQL soit prêt..."
  sleep 3
done

echo "⏳ MySQL prêt, démarrage des dumps..."

docker exec mysql-container mysqldump -u root -proot airlockunlock > "$BACKUP_DIR/back_airlockunlock.sql"
docker exec mysql-container mysqldump -u root -proot Tapkey > "$BACKUP_DIR/back_tapkey.sql"

echo "✅ Bases sauvegardées dans $BACKUP_DIR"

# Arrêt des containers
echo "⏬ Arrêt des conteneurs API et Web..."
cd APIs && docker-compose down && cd ..
cd Web && docker-compose down && cd ..

# Nettoyage des fichiers non suivis dans APIs/code/vendor pour éviter conflits git
echo "🧹 Nettoyage des fichiers non suivis dans APIs/code/vendor..."
git clean -fd APIs/code/vendor/

# Commit et push global (branche Deploiement)
echo "⬆️ Push Git global vers Deploiement"
git add .
git commit -m "Sauvegarde des bases et arrêt des conteneurs" || echo "Rien à committer"
git push origin Deploiement

# Commit et push uniquement le dossier APIs vers la branche API-1
echo "⬆️ Push du dossier APIs vers la branche API-1"
git add APIs/
git commit -m "Mise à jour APIs - sauvegarde et arrêt" || echo "Rien à committer pour APIs"
git push origin API-1

echo "✅ Fin du script docker-down.sh"
