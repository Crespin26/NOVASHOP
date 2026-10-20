# NovaShop

Plateforme e-commerce Full Stack conçue pour permettre aux entreprises de commercialiser leurs produits en ligne avec Laravel, Vue.js et Stripe.

> **Statut : fondations Full Stack.** Le frontend Vue et le backend Laravel 13 sont connectés, avec PostgreSQL 17, Sanctum et une CI configurée. Le catalogue reste fictif ; l’authentification complète, les commandes et Stripe restent à développer. Aucune transaction réelle n’est effectuée. Le tableau ci-dessous décrit le périmètre métier cible.

## Fonctionnalités prévues

| Domaine | Périmètre | État |
| --- | --- | --- |
| Catalogue | Produits, images, prix et fiches détaillées | À développer |
| Recherche | Recherche textuelle, catégories, prix, tri et pagination | À développer |
| Catégories | Création, modification et classement des produits | À développer |
| Panier | Ajout, modification des quantités et suppression | À développer |
| Commandes | Validation, historique et détail des achats | À développer |
| Authentification | Inscription, connexion, déconnexion et récupération du mot de passe | À développer |
| Paiement | Checkout hébergé par Stripe et confirmation par webhook | À développer |
| Stocks | Disponibilités, réservations et prévention des ventes excédentaires | À développer |
| Administration | Produits, catégories, stocks, commandes et indicateurs | À développer |
| Suivi | États de préparation, expédition et livraison | À développer |

## Stack et intégrations

- **Backend :** Laravel 13, PHP 8.5, API JSON versionnée et Sanctum préparé pour les sessions.
- **Frontend :** Vue 3, Vite et Vue Router 4 ; pages catégorie, filtres combinés et pagination dans l’URL. Pinia sera évalué avec les modules concernés.
- **Données :** PostgreSQL 17 ; files de tâches en base et cache fichier pour le développement.
- **Paiement :** Stripe Checkout et webhooks.
- **Qualité :** PHPUnit sur PostgreSQL, tests Node et CI GitHub Actions configurée ; parcours métier de bout en bout à venir.

Le prototype utilise Vue 3, Vite et Lucide. L’accueil et les listes promotions/nouveautés utilisent les mêmes produits : réductions calculées et dates de publication fictives. La sélection des nouveautés repose sur un instantané daté du 19 octobre 2026, pour une démonstration reproductible. Les données, prix, stocks et avis sont fictifs. Les visuels proviennent d’Unsplash et les polices de Google Fonts ; leur chargement nécessite une connexion Internet.

## Démarrer le projet

Prérequis : Docker avec Compose, Node.js 22 ou supérieur et npm. Docker fournit PHP et PostgreSQL ; les dépendances sont verrouillées dans `backend/composer.lock` et `frontend/package-lock.json`.

Depuis la racine :

```bash
./scripts/setup.sh
npm run dev --prefix frontend
```

Le script crée les `.env` manquants sans écraser ceux qui existent, construit PHP, installe les dépendances, génère la clé Laravel uniquement si elle manque, applique les migrations et démarre l’API. Le premier lancement nécessite Internet.

- Boutique : http://localhost:5173
- API : http://localhost:8000/api/v1/health
- Vérification PostgreSQL : http://localhost:8000/api/v1/ready
- PostgreSQL local : `127.0.0.1:5433`, base `novashop` ; identifiants de développement dans `.env.example`.

Sous Linux, adapter `LOCAL_UID` et `LOCAL_GID` dans le `.env` racine si votre utilisateur n’utilise pas `1000:1000`. Les ports sont configurables via `API_PORT` et `DB_FORWARD_PORT` ; si le port API change, adapter `frontend/.env` (`API_PROXY_TARGET`).

Pour les lancements suivants :

```bash
make up
make dev
```

Vite transmet `/api` et `/sanctum` à Laravel sous la même origine navigateur. Utiliser le même nom d’hôte (`localhost` ou `127.0.0.1`) pendant une session. Un indicateur de connexion à l’API et à PostgreSQL apparaît uniquement en mode développement. Après modification de `backend/.env`, redémarrer l’API avec `docker compose restart api` ; le rechargement automatique de cet environnement est désactivé pour préserver les variables Docker.

### Vérification

```bash
make test
make build
```

Les tests backend utilisent exclusivement la base séparée `novashop_testing`. Une garde empêche leur exécution sur la base de développement. La CI reproduit les tests sur PostgreSQL 17, puis compile le frontend ; son exécution distante sera vérifiable après publication du dépôt sur GitHub.

### Services et commandes utiles

```bash
make logs        # Journaux de l’API
make jobs        # Active worker et planificateur optionnels
make down        # Arrête les services et conserve les données
```

L’environnement Compose utilise le serveur de développement PHP et des identifiants locaux ; il est destiné au développement. Le déploiement de production sera configuré séparément, avec HTTPS, secrets propres à l’hébergement et débogage désactivé.

Le panier et les favoris restent dans le stockage local du navigateur. Le formulaire newsletter simule uniquement une inscription, sans transmission ni conservation d’adresse. Les formulaires de connexion, inscription et récupération sont des démonstrations sans transmission ni stockage des données saisies. L’espace compte public présente un profil et des adresses fictifs modifiables en mémoire seulement. La réinitialisation, la vérification e-mail et le paiement indiquent les intégrations serveur encore nécessaires.

L’en-tête et le footer partagent leur navigation, avec menus ordinateur/mobile, recherche, favoris et accès au tiroir panier. La page `/panier`, accessible depuis le tiroir et le footer, partage ses quantités et ses totaux ; elle permet de retirer les articles ou de vider le panier après confirmation. Les destinations encore absentes sont indiquées « Bientôt » ; le compte reste un aperçu.

Les pages publiques disposent d’URL directes : `/`, `/boutique`, `/categories/high-tech`, `/promotions`, `/nouveautes`, `/favoris` et `/produits/casque-sans-fil-studio`. Exemples de liens partageables : `/categories/high-tech?min=5000&max=10000&stock=in-stock` (prix en centimes) et `/boutique?page=2`. Les listes affichent 8 produits par page. Les formulaires de prix acceptent les euros avec virgule ou point. Deux produits fictifs sont en rupture pour vérifier ce parcours. Les produits inconnus et chemins inexistants affichent une page introuvable.

Le routage ne remplace pas les autorisations Laravel. Compte et checkout restent des aperçus sans authentification ni paiement ; aucune zone administrateur n’est ouverte par le routeur. Voir les [mesures de sécurité et limites du routage](docs/security-and-quality.md#routage-public-et-stockage-local).

Voir le [guide de l’interface](docs/interface.md) et le [README backend](backend/README.md) pour le contrat API, les tests et la base de données.

## Documentation

- [Plan des pages, accès et navigation — référence de finalisation](docs/pages-and-navigation.md)
- [Spécifications fonctionnelles](docs/specifications.md)
- [Architecture et modèle de données](docs/architecture.md)
- [Sécurité, paiement et stratégie de tests](docs/security-and-quality.md)
- [Plan de développement](docs/roadmap.md)

## Présentation

**Description courte pour GitHub — à utiliser lorsque l’application sera implémentée :**

> Modern full-stack e-commerce platform built with Laravel and Vue.js, featuring product management, shopping cart, secure checkout, and admin dashboard.

**Description adaptée au statut actuel :**

> Full-stack e-commerce project with a Vue.js storefront, Laravel API and PostgreSQL foundation. Commerce and Stripe integration in progress.

**Portfolio — description cible :**

> Plateforme e-commerce complète intégrant un catalogue dynamique, la gestion des commandes, le paiement en ligne et un espace d'administration.

**Nom de dépôt recommandé :** `novashop-ecommerce`.

## Publication et licence

Une publication publique peut valoriser le projet si les droits sur le code, les visuels et les contenus sont détenus. La visibilité du dépôt reste à configurer par son propriétaire. Aucune licence n’a encore été choisie ; la présence publique du code ne constitue pas une autorisation générale de réutilisation.

Ne jamais publier de secrets, de données clients, de clés Stripe ou de fichiers `.env` réels. Utiliser uniquement des données fictives pour les démonstrations.

Le parcours `/commande` propose trois étapes de démonstration, puis une confirmation et un suivi illustratif dans le compte. Les simulations restent en mémoire jusqu’au rechargement, sans coordonnées conservées, commande réelle ni paiement Stripe. Le panier est conservé.

Les pages aide/FAQ, contact, livraison, retours et informations légales sont accessibles depuis le footer. La FAQ est filtrable ; le formulaire de contact simule un envoi sans transmission ni stockage. Les informations légales et commerciales restent provisoires jusqu’à validation des données réelles de l’exploitant.

L’aperçu public `/admin` propose 16 écrans de gestion dans un layout distinct. Les données administratives fictives restent en mémoire et sont isolées de la boutique. Aucune action ne modifie des données réelles ; les autorisations serveur seront ajoutées avec les API métier.
