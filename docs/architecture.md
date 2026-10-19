# Architecture cible

## Organisation

Architecture proposée : monorepo avec un backend Laravel dans `backend/` et une application Vue dans `frontend/`. Ces dossiers sont désormais initialisés. Laravel 13 s’exécute avec PHP 8.5 et PostgreSQL 17 via Docker Compose. Le frontend et l’API seront servis sous une même origine pour simplifier les cookies de session et la protection CSRF.

Laravel porte les validations, les autorisations, les calculs de prix, les transactions de stock et l’intégration Stripe. Vue affiche les données et gère l’interaction. Les tâches différées traitent les notifications et les rapprochements ; elles ne remplacent pas les garanties transactionnelles.

## Modèle de données prévu

| Entité | Données et contraintes principales |
| --- | --- |
| `users` | Nom, email unique, mot de passe haché, rôle client ou administrateur |
| `categories` | Nom, slug unique |
| `products` | Catégorie, nom, slug unique, description, prix entier en unité monétaire mineure, devise, stock physique, état publié |
| `product_images` | Produit, chemin de stockage, texte alternatif, position |
| `carts` | Utilisateur ou identifiant de session visiteur |
| `cart_items` | Panier, produit, quantité ; unicité panier/produit |
| `orders` | Utilisateur, référence unique, états de paiement et livraison, devise, sous-total, taxes, livraison, total, adresse figée |
| `order_items` | Commande, produit éventuellement supprimé, nom et prix figés, quantité |
| `stock_reservations` | Commande, produit, quantité, échéance, état ; unicité commande/produit |
| `payments` | Commande, tentative unique, identifiant de session Stripe unique, montant, devise, état |
| `webhook_events` | Identifiant Stripe unique, type, état de traitement, dates |
| `order_status_history` | Commande, ancien et nouvel état, auteur, date |
| `stock_movements` | Produit, variation, motif, commande éventuelle, auteur, date |

Les montants n’utilisent pas de nombres flottants. Les commandes conservent leurs instantanés même après archivage d’un produit. Le stock disponible correspond au stock physique moins les réservations actives. Les transactions verrouillent les lignes des produits dans un ordre stable afin de limiter les interblocages.

Au paiement confirmé, le stock physique diminue et la réservation est consommée dans la même transaction. Une expiration libère la réservation sans modifier le stock physique. Chaque opération est idempotente et journalisée.

## Surface API indicative

Les routes métier ci-dessous sont proposées et restent à implémenter. Les fondations exposent déjà `GET /api/v1/health`, `GET /api/v1/ready` et `GET /api/v1/me` (protégée par Sanctum). Les futures routes métier utiliseront également le préfixe `/api/v1`. Un contrat OpenAPI sera ajouté avec ces modules :

| Méthode et route | Accès | Usage |
| --- | --- | --- |
| `GET /api/products` | Public | Catalogue filtré et paginé |
| `GET /api/products/{slug}` | Public | Fiche produit publié |
| `GET /api/categories` | Public | Catégories |
| `POST /register`, `POST /login`, `POST /logout` | Selon session | Authentification avec protection CSRF |
| `GET /api/me` | Client | Profil courant |
| `GET /api/cart` | Session | Contenu et totaux recalculés |
| `POST /api/cart/items` | Session | Ajout d’un produit |
| `PATCH /api/cart/items/{id}` | Propriétaire | Modification de quantité |
| `DELETE /api/cart/items/{id}` | Propriétaire | Retrait |
| `POST /api/checkout` | Client | Commande et session Stripe idempotentes |
| `GET /api/orders` | Client | Historique personnel |
| `GET /api/orders/{reference}` | Propriétaire | Détail et suivi |
| `POST /api/webhooks/stripe` | Signature Stripe | Réception d’événements |
| `/api/admin/products`, `/api/admin/categories` | Administrateur | Gestion du catalogue |
| `POST /api/admin/products/{id}/stock-adjustments` | Administrateur | Ajustement de stock journalisé |
| `GET /api/admin/orders` | Administrateur | Gestion des commandes |
| `PATCH /api/admin/orders/{reference}/fulfillment` | Administrateur | Transition de livraison |
| `GET /api/admin/dashboard` | Administrateur | Indicateurs |

Les opérations de création de checkout acceptent une clé d’idempotence liée à l’utilisateur et au contenu validé. Une réutilisation avec un contenu différent est refusée. Les erreurs utilisent une structure JSON cohérente et les codes HTTP appropriés.

## Configuration à prévoir

Les fichiers `.env.example` documentent la connexion à la base, l’URL publique, les domaines de session, les files de tâches et les paramètres Stripe. Seule la clé publiable Stripe peut être exposée au frontend ; les secrets Stripe et de webhook restent côté serveur. Aucun secret de démonstration réel ne sera commité.
