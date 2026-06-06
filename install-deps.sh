#!/bin/bash
set -e

echo "🔄 Génération des dépendances Composer..."

# Créer le vendor si n'existe pas
if [ ! -d "vendor" ]; then
    mkdir -p vendor
fi

# Utiliser l'image Composer pour installer les dépendances
# Cette commande fonctionne même sans Docker daemon
docker run --rm \
  -v "$(pwd):/app" \
  -w /app \
  --user $(id -u):$(id -g) \
  composer:2.8 \
  composer install --no-dev --optimize-autoloader

echo "✅ Dépendances installées avec succès!"
echo "📦 Fichiers générés: vendor/, composer.lock"
