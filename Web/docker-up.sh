#!/bin/bash

echo "📦 Lancement des containers..."
docker-compose up -d

echo "⏳ Attente du démarrage de MySQL..."
sleep 10  # tu peux ajuster le délai ou utiliser un healthcheck plus avancé

