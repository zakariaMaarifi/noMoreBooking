# NoMoreBook

## Présentation

NoMoreBook est une plateforme de réservation d’hôtels avec gestion multi-profils (admin, partenaire, client) développée en Symfony 6 et Tailwind CSS.

---
## Diagramme de classes

          +---------------------+
          |      User           |  <abstract>
          +---------------------+
          | - id: int           |
          | - email: string     |
          | - roles: array      |
          | - password: string  |
          +---------------------+
                 /   |    \
                /    |     \
               /     |      \
+-----------+   +-----------+   +-----------+
|  Client   |   |  Partner  |   |  Admin    |
+-----------+   +-----------+   +-----------+
| - firstName   | - companyName | - superAdmin
| - lastName    |               |
+-----------+   +-----------+   +-----------+

         +---------------------+
         |       Hotel         |
         +---------------------+
         | - id: int           |
         | - name: string      |
         | - city: string      |
         | - status: string    |
         +---------------------+
         | * partner: Partner  |
         | * reservations: Reservation[]
         | * reviews: Review[]
         +---------------------+

         +---------------------+
         |    Reservation      |
         +---------------------+
         | - id: int           |
         | - startDate: Date   |
         | - endDate: Date     |
         | - price: float      |
         | - isAvailable: bool |
         +---------------------+
         | * hotel: Hotel      |
         | * client: Client    |
         | * partner: Partner  |
         | * categories: Category[]
         +---------------------+

         +---------------------+
         |     Category        |
         +---------------------+
         | - id: int           |
         | - name: string      |
         +---------------------+
         | * reservations: Reservation[]
         +---------------------+

         +---------------------+
         |      Review         |
         +---------------------+
         | - id: int           |
         | - rating: int       |
         | - comment: string   |
         +---------------------+
         | * hotel: Hotel      |
         | * client: Client    |
         +---------------------+

         +---------------------+
         |     Purchase        |
         +---------------------+
         | - id: int           |
         | - purchasedAt: Date |
         +---------------------+
         | * client: Client    |
         | * reservation: Reservation
         +---------------------+

         +---------------------+
         |      Message        |
         +---------------------+
         | - id: int           |
         | - content: string   |
         | - sentAt: Date      |
         +---------------------+
         | * sender: User      |
         | * receiver: User    |
         +---------------------+

## Cahier des charges (extrait)

- **Utilisateurs** :  
  - Inscription/connexion pour clients et partenaires  
  - Rôles : Admin, Partenaire, Client
- **Partenaires** :  
  - Gestion de leurs hôtels, disponibilités, réservations, discussions avec clients
- **Clients** :  
  - Recherche, réservation, messagerie avec partenaires
- **Admin** :  
  - Dashboard EasyAdmin pour gérer tous les utilisateurs, hôtels, réservations, etc.
- **Messagerie** :  
  - Système de discussion entre clients et partenaires
- **Front-end** :  
  - Tailwind CSS, Webpack Encore

---

## Prérequis

- PHP >= 8.1
- Composer
- MySQL ou MariaDB
- Node.js >= 16 et npm
- [Symfony CLI (optionnel mais recommandé)](https://symfony.com/download)

---

## Installation

1. **Clone le projet**
   ```sh
   git clone https://github.com/zakariaMaarifi/noMoreBooking.git
   cd noMoreBook
   ```

2. **Installe les dépendances PHP**
   ```sh
   composer install
   ```

3. **Installe les dépendances front-end**
   ```sh
   npm install
   ```

4. **Configure la base de données**
   - Modifie `.env.local` avec tes identifiants MySQL :
     ```
     DATABASE_URL="mysql://root:@127.0.0.1:3306/no_more_book?serverVersion=16&charset=utf8"
     ```
   - Crée la base si besoin :
     ```sh
     php bin/console doctrine:database:create
     ```

5. **Exécute les migrations**
   ```sh
   php bin/console doctrine:migrations:migrate
   ```

6. **Compile les assets front-end**
   ```sh
   npm run dev
   ```
   (ou `npm run build` pour la prod)

---

## Démarrage

- **Lance le serveur Symfony**
  ```sh
  symfony serve
  ```
  ou
  ```sh
  php -S 127.0.0.1:8000 -t public
  ```

- **Accède à l’application**
  - Front-office : [http://localhost:8000/](http://localhost:8000/)
  - Back-office admin : [http://localhost:8000/admincrud](http://localhost:8000/admincrud)

---

## Accès admin

- Par défaut, crée un utilisateur admin via EasyAdmin ou directement en base de données.

---

## Dépannage

- Si tu as une erreur sur les assets :  
  - Vérifie que tu as bien fait `npm install` puis `npm run dev`
- Si tu as une erreur de connexion MySQL :  
  - Vérifie la variable `DATABASE_URL` dans `.env.local`
- Si tu as une erreur sur l’extension `intl` :  
  - Active l’extension dans ton `php.ini`

---

## Structure du projet

- `src/Entity` : Entités Doctrine (User, Client, Partner, Hotel, Reservation, Message…)
- `src/Controller` : Contrôleurs Symfony
- `templates/` : Vues Twig
- `assets/` : JS/CSS (gérés par Webpack Encore)
- `public/` : Fichiers publics (index.php, build/…)

---


