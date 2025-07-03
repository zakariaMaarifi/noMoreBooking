#!/bin/bash

# Script de déploiement Docker pour NoMoreBooking

set -e

echo "🚀 Déploiement de NoMoreBooking avec Docker"

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Vérifier si Docker est installé
if ! command -v docker &> /dev/null; then
    log_error "Docker n'est pas installé. Veuillez installer Docker d'abord."
    exit 1
fi

# Vérifier si Docker Compose est installé
if ! command -v docker-compose &> /dev/null; then
    log_error "Docker Compose n'est pas installé. Veuillez installer Docker Compose d'abord."
    exit 1
fi

# Mode de déploiement (production par défaut)
MODE=${1:-prod}

if [ "$MODE" = "dev" ]; then
    log_info "Déploiement en mode développement"
    COMPOSE_FILE="docker-compose.dev.yml"
else
    log_info "Déploiement en mode production"
    COMPOSE_FILE="docker-compose.yml"
fi

# Arrêter les conteneurs existants
log_info "Arrêt des conteneurs existants..."
docker-compose -f $COMPOSE_FILE down

# Construire les images
log_info "Construction des images Docker..."
docker-compose -f $COMPOSE_FILE build --no-cache

# Démarrer les services
log_info "Démarrage des services..."
docker-compose -f $COMPOSE_FILE up -d

# Attendre que la base de données soit prête
log_info "Attente de la base de données..."
sleep 30

# Exécuter les migrations
log_info "Exécution des migrations de base de données..."
if [ "$MODE" = "dev" ]; then
    docker-compose -f $COMPOSE_FILE exec app-dev php bin/console doctrine:migrations:migrate --no-interaction
else
    docker-compose -f $COMPOSE_FILE exec app php bin/console doctrine:migrations:migrate --no-interaction
fi

# Charger les fixtures en mode dev
if [ "$MODE" = "dev" ]; then
    log_info "Chargement des fixtures..."
    docker-compose -f $COMPOSE_FILE exec app-dev php bin/console doctrine:fixtures:load --no-interaction || log_warning "Fixtures non disponibles"
fi

log_info "✅ Déploiement terminé !"
echo ""
log_info "🌐 Application disponible sur: http://localhost:8080"
log_info "🗄️  PhpMyAdmin disponible sur: http://localhost:8081"
echo ""
log_info "Pour voir les logs: docker-compose -f $COMPOSE_FILE logs -f"
log_info "Pour arrêter: docker-compose -f $COMPOSE_FILE down"
