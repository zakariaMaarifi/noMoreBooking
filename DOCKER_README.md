# NoMoreBooking - Déploiement Docker 🐳

Ce projet est maintenant entièrement dockerisé pour faciliter le déploiement et le développement.

## 🚀 Démarrage rapide

### Prérequis
- Docker
- Docker Compose

### Installation et démarrage

```bash
# Mode production
./deploy.sh prod
# ou
make install

# Mode développement
./deploy.sh dev
# ou
make install-dev
```

## 📋 Commandes disponibles

### Avec le Makefile
```bash
make help          # Afficher l'aide
make build         # Construire les images
make up            # Démarrer (production)
make dev           # Démarrer (développement)
make down          # Arrêter
make logs          # Voir les logs
make shell         # Accéder au conteneur app
make db-shell      # Accéder à MySQL
make migrations    # Exécuter les migrations
make fixtures      # Charger les fixtures (dev)
make clean         # Nettoyer tout
make restart       # Redémarrer
make status        # Voir le statut
```

### Avec Docker Compose

#### Production
```bash
# Démarrer
docker-compose up -d

# Arrêter
docker-compose down

# Voir les logs
docker-compose logs -f

# Accéder au conteneur
docker-compose exec app bash
```

#### Développement
```bash
# Démarrer
docker-compose -f docker-compose.dev.yml up -d

# Arrêter
docker-compose -f docker-compose.dev.yml down
```

## 🌐 Accès aux services

- **Application web**: http://localhost:8080
- **PhpMyAdmin**: http://localhost:8081
- **Base de données**: localhost:3307

### Connexion à la base de données
- **Host**: localhost (ou `db` depuis les conteneurs)
- **Port**: 3307 (depuis l'hôte) / 3306 (entre conteneurs)
- **Database**: nomorebook_db
- **Username**: nomorebook_user
- **Password**: nomorebook_password

## 🔧 Configuration

### Variables d'environnement

Les variables d'environnement sont définies dans :
- `docker-compose.yml` (production)
- `docker-compose.dev.yml` (développement)
- `.env.docker` (template)

### Personnalisation

1. **Modifier les ports** : Éditez les sections `ports` dans les fichiers docker-compose
2. **Modifier la base de données** : Changez les variables d'environnement MySQL
3. **Ajouter des services** : Ajoutez de nouveaux services dans docker-compose.yml

## 📁 Structure Docker

```
.
├── Dockerfile              # Image production
├── Dockerfile.dev          # Image développement
├── docker-compose.yml      # Orchestration production
├── docker-compose.dev.yml  # Orchestration développement
├── .dockerignore           # Fichiers à ignorer
├── .env.docker            # Variables d'environnement
├── deploy.sh              # Script de déploiement
├── Makefile               # Commandes simplifiées
└── noMoreBook/            # Code source Symfony
```

## 🐛 Dépannage

### Problèmes courants

1. **Port déjà utilisé**
   ```bash
   # Changer le port dans docker-compose.yml
   ports:
     - "8081:80"  # au lieu de 8080:80
   ```

2. **Permissions**
   ```bash
   # Reconstruire avec les bonnes permissions
   docker-compose build --no-cache
   ```

3. **Base de données non accessible**
   ```bash
   # Vérifier que le conteneur DB est démarré
   docker-compose ps
   
   # Voir les logs de la DB
   docker-compose logs db
   ```

4. **Erreurs de migration**
   ```bash
   # Accéder au conteneur et debugger
   docker-compose exec app bash
   php bin/console doctrine:migrations:status
   ```

### Commandes de maintenance

```bash
# Nettoyer tout
make clean

# Voir l'utilisation des ressources
docker system df

# Nettoyer les images inutilisées
docker image prune

# Redémarrer complètement
make clean && make install
```

## 🔄 Workflow de développement

1. **Développement local**
   ```bash
   make dev
   # Les fichiers sont synchronisés en temps réel
   ```

2. **Test des changements**
   ```bash
   make logs  # Voir les erreurs
   ```

3. **Déploiement en production**
   ```bash
   make clean
   make prod
   ```

## 📊 Monitoring

### Vérifier la santé des conteneurs
```bash
make status
docker-compose ps
```

### Voir les logs en temps réel
```bash
make logs
# ou pour un service spécifique
docker-compose logs -f app
```

### Surveillance des ressources
```bash
docker stats
```

## 🚢 Déploiement en production

1. Modifier les variables d'environnement dans `docker-compose.yml`
2. Changer les mots de passe par défaut
3. Configurer HTTPS (reverse proxy recommandé)
4. Configurer les sauvegardes de base de données

```bash
# Production
APP_SECRET=your-production-secret
MYSQL_ROOT_PASSWORD=strong-password
MYSQL_PASSWORD=strong-password
```
