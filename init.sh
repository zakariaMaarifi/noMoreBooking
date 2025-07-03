#!/bin/bash

# Script d'initialisation pour NoMoreBooking Docker

set -e

echo "🔧 Initialisation de l'environnement NoMoreBooking"

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

# Vérifier les prérequis
log_info "Vérification des prérequis..."

if ! command -v docker &> /dev/null; then
    log_error "Docker n'est pas installé. Installation nécessaire."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    log_error "Docker Compose n'est pas installé. Installation nécessaire."
    exit 1
fi

log_info "✅ Docker et Docker Compose sont installés"

# Vérifier que Docker est en fonctionnement
if ! docker info &> /dev/null; then
    log_error "Docker n'est pas en cours d'exécution. Veuillez démarrer Docker."
    exit 1
fi

log_info "✅ Docker est en cours d'exécution"

# Créer les fichiers d'environnement s'ils n'existent pas
if [ ! -f "noMoreBook/.env" ]; then
    log_warning "Fichier .env manquant, création d'un fichier par défaut..."
    cp noMoreBook/.env.dev noMoreBook/.env
fi

# Vérifier les permissions
log_info "Vérification des permissions..."
chmod +x deploy.sh

# Nettoyer les anciens conteneurs si ils existent
log_info "Nettoyage des anciens conteneurs..."
docker-compose down -v --remove-orphans 2>/dev/null || true
docker-compose -f docker-compose.dev.yml down -v --remove-orphans 2>/dev/null || true

# Affichage des informations
echo ""
log_info "🎉 Initialisation terminée !"
echo ""
echo "Commandes disponibles :"
echo "  ./deploy.sh prod     - Déployer en production"
echo "  ./deploy.sh dev      - Déployer en développement"
echo "  make help           - Voir toutes les commandes"
echo ""
echo "Pour commencer :"
echo "  ${GREEN}./deploy.sh dev${NC}     (développement)"
echo "  ${GREEN}./deploy.sh prod${NC}    (production)"
