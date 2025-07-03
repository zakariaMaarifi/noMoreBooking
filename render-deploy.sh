#!/bin/bash

# Script de post-déploiement pour Render.com
set -e

echo "🚀 Post-déploiement NoMoreBooking sur Render"

# Attendre que la base de données soit disponible
echo "⏳ Attente de la base de données..."
sleep 10

# Exécuter les migrations
echo "📊 Exécution des migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Optionnel: Charger des données de base
echo "📥 Chargement des données de base..."
php bin/console doctrine:fixtures:load --no-interaction --append || echo "Pas de fixtures disponibles"

# Nettoyer et réchauffer le cache
echo "🔥 Nettoyage du cache..."
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

echo "✅ Post-déploiement terminé !"
