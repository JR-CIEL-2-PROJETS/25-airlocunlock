#!/bin/bash

echo "📦 Lancement des containers..."
docker-compose up -d

echo "⏳ Attente du démarrage de MySQL..."
sleep 10  # tu peux ajuster le délai ou utiliser un healthcheck plus avancé

echo "📂 Import des bases de données..."
docker exec -i mysql-container mysql -u root -proot airlockunlock < code/back_airlockunlock.sql
docker exec -i mysql-container mysql -u root -proot Tapkey < code/back_tapkey.sql

echo "✅ Bases importées."
