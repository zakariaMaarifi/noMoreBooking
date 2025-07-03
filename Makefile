# Makefile pour NoMoreBooking Docker

.PHONY: help build up down logs shell db-shell clean restart dev prod

# Couleurs
YELLOW := \033[33m
GREEN := \033[32m
RED := \033[31m
NC := \033[0m

help: ## Afficher cette aide
	@echo "$(GREEN)NoMoreBooking - Commandes Docker$(NC)"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "$(YELLOW)%-15s$(NC) %s\n", $$1, $$2}'

build: ## Construire les images Docker
	@echo "$(GREEN)Construction des images...$(NC)"
	docker-compose build --no-cache

up: ## Démarrer les services (production)
	@echo "$(GREEN)Démarrage des services en production...$(NC)"
	docker-compose up -d

down: ## Arrêter les services
	@echo "$(RED)Arrêt des services...$(NC)"
	docker-compose down

logs: ## Afficher les logs
	docker-compose logs -f

shell: ## Accéder au shell du conteneur app
	docker-compose exec app bash

db-shell: ## Accéder au shell MySQL
	docker-compose exec db mysql -u nomorebook_user -p nomorebook_db

clean: ## Nettoyer les conteneurs et volumes
	@echo "$(RED)Nettoyage...$(NC)"
	docker-compose down -v
	docker system prune -f

restart: down up ## Redémarrer les services

dev: ## Démarrer en mode développement
	@echo "$(GREEN)Démarrage en mode développement...$(NC)"
	docker-compose -f docker-compose.dev.yml up -d

dev-down: ## Arrêter le mode développement
	docker-compose -f docker-compose.dev.yml down

prod: up ## Démarrer en mode production

migrations: ## Exécuter les migrations
	docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction

fixtures: ## Charger les fixtures (dev only)
	docker-compose -f docker-compose.dev.yml exec app-dev php bin/console doctrine:fixtures:load --no-interaction

install: ## Installation complète
	@echo "$(GREEN)Installation complète...$(NC)"
	./deploy.sh prod

install-dev: ## Installation en mode développement
	@echo "$(GREEN)Installation en mode développement...$(NC)"
	./deploy.sh dev

status: ## Voir le statut des conteneurs
	docker-compose ps

# Commandes spécifiques à Render
render-check: ## Vérifier avant déploiement Render
	@echo "$(GREEN)Vérification avant déploiement Render...$(NC)"
	./pre-deploy-check.sh

render-test: ## Tester l'environnement Render en local
	@echo "$(GREEN)Test de l'environnement Render...$(NC)"
	./test-render.sh

render-build: ## Construire l'image Render
	@echo "$(GREEN)Construction de l'image Render...$(NC)"
	docker build -f Dockerfile.render -t nomorebook-render .

render-deploy: render-check ## Préparer le déploiement Render
	@echo "$(GREEN)Préparation du déploiement Render...$(NC)"
	git add .
	git commit -m "Ready for Render deployment" || true
	git push origin main
	@echo "$(YELLOW)Maintenant, configurez votre service sur render.com$(NC)"
