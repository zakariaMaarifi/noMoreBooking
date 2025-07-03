#!/bin/bash

# Script de diagnostic pour problèmes de déploiement Render

echo "🔍 Diagnostic des problèmes Render"
echo "================================="

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

echo ""
echo "📁 Structure des fichiers :"
echo "----------------------------"

# Vérifier la structure des fichiers
if [ -f "Dockerfile.render" ]; then
    log_info "Dockerfile.render trouvé à la racine"
else
    log_error "Dockerfile.render MANQUANT à la racine"
fi

if [ -f "render.yaml" ]; then
    log_info "render.yaml trouvé"
    
    # Vérifier le contenu de render.yaml
    echo ""
    echo "🔧 Configuration render.yaml :"
    echo "-----------------------------"
    
    if grep -q "dockerfilePath:" render.yaml; then
        DOCKERFILE_PATH=$(grep "dockerfilePath:" render.yaml | sed 's/.*dockerfilePath: *//')
        echo "   dockerfilePath: $DOCKERFILE_PATH"
        
        if [ "$DOCKERFILE_PATH" = "Dockerfile.render" ]; then
            log_info "Chemin Dockerfile correct"
        else
            log_warning "Chemin Dockerfile pourrait être incorrect: $DOCKERFILE_PATH"
        fi
    fi
    
    if grep -q "dockerContext:" render.yaml; then
        DOCKER_CONTEXT=$(grep "dockerContext:" render.yaml | sed 's/.*dockerContext: *//')
        echo "   dockerContext: $DOCKER_CONTEXT"
    else
        log_warning "dockerContext non défini (sera '.' par défaut)"
    fi
else
    log_error "render.yaml MANQUANT"
fi

echo ""
echo "📂 Contenu du dossier noMoreBook :"
echo "--------------------------------"

if [ -d "noMoreBook" ]; then
    log_info "Dossier noMoreBook trouvé"
    
    if [ -f "noMoreBook/composer.json" ]; then
        log_info "composer.json présent"
    else
        log_error "composer.json MANQUANT"
    fi
    
    if [ -f "noMoreBook/package.json" ]; then
        log_info "package.json présent"
    else
        log_error "package.json MANQUANT"
    fi
    
    if [ -d "noMoreBook/src" ]; then
        log_info "Dossier src/ présent"
    else
        log_error "Dossier src/ MANQUANT"
    fi
    
    if [ -d "noMoreBook/public" ]; then
        log_info "Dossier public/ présent"
    else
        log_error "Dossier public/ MANQUANT"
    fi
else
    log_error "Dossier noMoreBook MANQUANT"
fi

echo ""
echo "🐳 Vérification Dockerfile :"
echo "----------------------------"

if [ -f "Dockerfile.render" ]; then
    # Vérifier les chemins COPY dans le Dockerfile
    echo "Chemins COPY dans Dockerfile.render :"
    grep "^COPY" Dockerfile.render | while read line; do
        echo "   $line"
    done
    
    # Vérifier si les chemins correspondent à la structure
    if grep -q "COPY noMoreBook/" Dockerfile.render; then
        log_info "Chemins COPY semblent corrects"
    else
        log_warning "Vérifiez les chemins COPY dans Dockerfile.render"
    fi
fi

echo ""
echo "💡 Solutions communes :"
echo "----------------------"
echo "1. Dockerfile.render doit être à la racine du projet"
echo "2. render.yaml doit avoir dockerfilePath: Dockerfile.render (sans ./)"
echo "3. Le dossier noMoreBook/ doit contenir votre application Symfony"
echo "4. Vérifiez que tous les fichiers sont committés sur GitHub"

echo ""
echo "🔗 Fichiers de référence :"
echo "-------------------------"
echo "   - RENDER_DEPLOYMENT_GUIDE.md (guide complet)"
echo "   - RENDER_QUICK_START.md (résumé rapide)"
echo ""
