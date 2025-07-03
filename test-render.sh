#!/bin/bash

# Script de test pour simuler l'environnement Render en local

set -e

echo "🧪 Test du déploiement Render en local"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Nettoyer les anciens conteneurs de test
log_info "Nettoyage des anciens conteneurs de test..."
docker stop nomorebook-render-test 2>/dev/null || true
docker rm nomorebook-render-test 2>/dev/null || true

# Construire l'image Render
log_info "Construction de l'image Render..."
docker build -f Dockerfile.render -t nomorebook-render-test .

# Démarrer PostgreSQL pour les tests
log_info "Démarrage de PostgreSQL pour les tests..."
docker run -d \
  --name postgres-render-test \
  -e POSTGRES_DB=no_more_book \
  -e POSTGRES_USER=nomorebook_user \
  -e POSTGRES_PASSWORD=test_password \
  -p 5433:5432 \
  postgres:13

# Attendre que PostgreSQL soit prêt
log_info "Attente de PostgreSQL..."
sleep 10

# Démarrer l'application
log_info "Démarrage de l'application..."
docker run -d \
  --name nomorebook-render-test \
  --link postgres-render-test \
  -e APP_ENV=prod \
  -e APP_DEBUG=0 \
  -e APP_SECRET=test-secret-key-for-render \
  -e DATABASE_URL="postgresql://nomorebook_user:test_password@postgres-render-test:5432/no_more_book" \
  -e MAILER_DSN="smtp://bc484694b30f0a:bdc8204781ab57@sandbox.smtp.mailtrap.io:2525" \
  -p 8080:80 \
  nomorebook-render-test

# Attendre que l'application démarre
log_info "Attente du démarrage de l'application..."
sleep 30

# Test de santé
log_info "Test de l'application..."
if curl -f http://localhost:8080 >/dev/null 2>&1; then
    log_info "✅ Application accessible sur http://localhost:8080"
else
    log_error "❌ Application non accessible"
    echo "Logs de l'application :"
    docker logs nomorebook-render-test
fi

# Affichage des logs
log_info "Derniers logs de l'application :"
docker logs --tail 20 nomorebook-render-test

echo ""
log_info "🎯 Test terminé !"
echo ""
echo "Commandes utiles :"
echo "  docker logs nomorebook-render-test     # Voir tous les logs"
echo "  docker exec -it nomorebook-render-test bash  # Accéder au conteneur"
echo "  curl http://localhost:8080             # Tester l'application"
echo ""
echo "Pour nettoyer :"
echo "  docker stop nomorebook-render-test postgres-render-test"
echo "  docker rm nomorebook-render-test postgres-render-test"
