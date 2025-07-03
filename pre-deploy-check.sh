#!/bin/bash

# Script de vérification avant déploiement sur Render

set -e

echo "🔍 Vérification avant déploiement sur Render"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log_info() {
    echo -e "${GREEN}[✓]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[⚠]${NC} $1"
}

log_error() {
    echo -e "${RED}[✗]${NC} $1"
}

ERRORS=0

# Vérifier les fichiers nécessaires
echo "📁 Vérification des fichiers..."

if [ -f "Dockerfile.render" ]; then
    log_info "Dockerfile.render présent à la racine"
    
    # Vérifier que le chemin dans render.yaml est correct
    if grep -q "dockerfilePath: Dockerfile.render" render.yaml 2>/dev/null; then
        log_info "Chemin Dockerfile correct dans render.yaml"
    else
        log_warning "Vérifiez le chemin dockerfilePath dans render.yaml"
    fi
else
    log_error "Dockerfile.render manquant à la racine"
    ERRORS=$((ERRORS + 1))
fi

if [ -f "render.yaml" ]; then
    log_info "render.yaml présent"
else
    log_error "render.yaml manquant"
    ERRORS=$((ERRORS + 1))
fi

if [ -f "noMoreBook/composer.json" ]; then
    log_info "composer.json présent"
else
    log_error "composer.json manquant"
    ERRORS=$((ERRORS + 1))
fi

if [ -f "noMoreBook/package.json" ]; then
    log_info "package.json présent"
else
    log_error "package.json manquant"
    ERRORS=$((ERRORS + 1))
fi

# Vérifier la configuration Symfony
echo ""
echo "⚙️  Vérification de la configuration Symfony..."

cd noMoreBook

if grep -q "pdo_pgsql" config/packages/doctrine.yaml 2>/dev/null || grep -q "PostgreSQLPlatform" config/packages/doctrine.yaml; then
    log_info "Support PostgreSQL configuré"
else
    log_warning "Support PostgreSQL pourrait ne pas être configuré"
fi

# Vérifier les migrations
if [ -d "migrations" ] && [ "$(ls -A migrations)" ]; then
    log_info "Migrations présentes"
else
    log_warning "Aucune migration trouvée"
fi

# Vérifier les assets
if [ -f "webpack.config.js" ]; then
    log_info "Configuration Webpack présente"
else
    log_warning "webpack.config.js manquant"
fi

cd ..

# Vérifier Git
echo ""
echo "📦 Vérification Git..."

if git status >/dev/null 2>&1; then
    log_info "Repository Git initialisé"
    
    if git remote -v | grep -q origin; then
        log_info "Remote origin configuré"
        echo "   Remote: $(git remote get-url origin)"
    else
        log_error "Remote origin non configuré"
        ERRORS=$((ERRORS + 1))
    fi
    
    if [ -n "$(git status --porcelain)" ]; then
        log_warning "Modifications non commitées détectées"
        git status --porcelain
    else
        log_info "Working directory propre"
    fi
else
    log_error "Repository Git non initialisé"
    ERRORS=$((ERRORS + 1))
fi

# Vérifier les variables d'environnement sensibles
echo ""
echo "🔐 Vérification des variables sensibles..."

if grep -r "password.*123" noMoreBook/ >/dev/null 2>&1 || \
   grep -r "secret.*test" noMoreBook/ >/dev/null 2>&1 || \
   grep -r "key.*dev" noMoreBook/ >/dev/null 2>&1; then
    log_warning "Mots de passe ou clés de test détectés - à changer en production"
fi

# Résumé
echo ""
echo "📊 Résumé de la vérification..."

if [ $ERRORS -eq 0 ]; then
    log_info "✅ Prêt pour le déploiement sur Render !"
    echo ""
    echo "Prochaines étapes :"
    echo "1. git add . && git commit -m 'Ready for Render deployment'"
    echo "2. git push origin main"
    echo "3. Configurer le service sur render.com"
    echo "4. Déployer avec le render.yaml"
else
    log_error "❌ $ERRORS erreur(s) détectée(s) - Corrigez avant de déployer"
    exit 1
fi

echo ""
echo "🔗 Liens utiles :"
echo "  - Guide de déploiement : ./RENDER_DEPLOYMENT_GUIDE.md"
echo "  - Test local : ./test-render.sh"
echo "  - Render Dashboard : https://dashboard.render.com"
