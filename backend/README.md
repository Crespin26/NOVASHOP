# NovaShop API

Backend Laravel 13 sur PHP 8.5 et PostgreSQL 17. Le frontend Vue est situé dans `../frontend/`.

Les commandes d’installation et de développement sont documentées dans le [README principal](../README.md). Docker Compose fournit PHP, son pilote PostgreSQL et une base persistante ; aucun service PostgreSQL système n’est nécessaire.

## API disponible

| Route | Comportement |
| --- | --- |
| `GET /api/v1/health` | Disponibilité de Laravel, sans dépendance à la base |
| `GET /api/v1/ready` | Vérification PostgreSQL ; HTTP 503 en cas d’échec |
| `GET /api/v1/me` | Profil de la session authentifiée ; HTTP 401 sinon |
| `GET /sanctum/csrf-cookie` | Initialisation des cookies CSRF et de session |

Les succès exposent `data`. Les erreurs utilisent `message` et, pour les validations à venir, `errors`. Les erreurs sur `/api/*` sont rendues en JSON même sans en-tête `Accept`. Limite initiale : 120 appels API par minute et par utilisateur ou adresse IP, sur le stockage local du cache. Un stockage partagé sera nécessaire avant d’exécuter plusieurs instances.

Sanctum est préparé pour les sessions de la SPA, avec chiffrement de session et protection CSRF. Les endpoints d’inscription et de connexion seront développés dans le module authentification. Aucun compte de démonstration n’est créé automatiquement.

## Tests

Depuis la racine du dépôt :

```bash
docker compose run --rm api vendor/bin/phpunit
```

Les tests utilisent exclusivement `novashop_testing`, créée avec le volume PostgreSQL initial. Une garde refuse toute autre base avant l’exécution des migrations de test. La base de développement est `novashop`.

Pour un volume déjà initialisé sans base de test, la créer explicitement :

```bash
docker compose exec postgres createdb -U novashop -O novashop novashop_testing
```

Cette commande ne doit être lancée que si la base n’existe pas.

## API métier

Les contrats, paramètres internationaux/TTC, règles d’accès et commandes de rapprochement sont décrits dans [docs/backend-api.md](../docs/backend-api.md). Les ventes sont désactivées par défaut ; aucune clé Stripe réelle ni offre de livraison mondiale n’est préconfigurée. Les contenus FR/EN restent des objets localisés. Les taux de règlement sont validés côté serveur et indépendants des conversions d’affichage Vue.

```bash
docker compose run --rm api php artisan migrate --no-interaction
docker compose run --rm api vendor/bin/phpunit
docker compose --profile jobs up -d worker scheduler
```

Exécuter les commandes Compose depuis la racine. Les tests utilisent uniquement `novashop_testing`, des notifications simulées et des requêtes Stripe factices. Le frontend n’est pas encore connecté à ces services.
