#!/bin/bash

echo "📦 Ajout, commit et push Git des fichiers SQL dans code/..."
git add .
git commit -m "save"
git push origin Web

echo "🧹 Arrêt des containers..."
docker-compose down

echo "✅ Bases sauvegardées dans, commit Git fait, containers arrêtés."