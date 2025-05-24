#!/bin/bash

echo "🔼 Lancement des conteneurs Docker..."
docker-compose up -d

echo "⏳ Attente du démarrage de MySQL..."
sleep 10

echo "📦 Importation des bases de données..."
docker exec mysql_container mysqldump -u root -pROOT_PASSWORD back_airlockunlock < APIs/code/back_airlockunlock.sql
docker exec mysql_container mysqldump -u root -pROOT_PASSWORD back_tapkey < APIs/code/back_tapkey.sql

echo "✅ Conteneurs lancés et bases importées."
