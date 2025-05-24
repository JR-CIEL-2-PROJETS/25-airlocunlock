#!/bin/bash

echo "🔼 Lancement des conteneurs Docker..."
docker-compose up -d

echo "⏳ Attente du démarrage de MySQL..."
sleep 10

echo "📦 Importation des bases de données..."
docker exec -i mysql_container mysql -u root -pROOT_PASSWORD back_airlockunlock < back_airlockunlock.sql
docker exec -i mysql_container mysql -u root -pROOT_PASSWORD back_tapkey < back_tapkey.sql

echo "✅ Conteneurs lancés et bases importées."
