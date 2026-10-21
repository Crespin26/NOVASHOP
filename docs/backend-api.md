# API métier NovaShop — v1

Les routes sont sous `/api/v1`. Les écrans Vue ne sont pas encore branchés à ces API (point 12). Les migrations sont à appliquer sur l’environnement de développement ; les tests utilisent exclusivement `novashop_testing`.

## Contrats communs

- JSON, `Accept: application/json`. Ressource `{ "data": ... }` ; listes paginées avec `data`, `links`, `meta`. Les validations exposent `message` et `errors` par champ.
- 200 lecture/modification, 201 création, 202 demande acceptée, 204 suppression, 401 session absente, 403 autorisation insuffisante, 404 ressource inconnue ou d’un autre client, 409 conflit métier, 419 CSRF, 422 validation, 429 limite de tentatives, 503 service ou règles commerciales non configurés.
- Pagination : `page` entier positif, tailles bornées ; catalogue `per_page` entre 1 et 50. Aucun fragment SQL ni ordre arbitraire n’est accepté.
- Contenus localisés `{ "fr": "...", "en": "..." }`. Les identifiants/slugs restent stables. `Accept-Language` sélectionne fr/en pour le contexte API ; les messages métier actuels sont principalement anglais, à mapper/traduire dans le client.
- Montants entiers en **unités mineures de la devise**, jamais flottants : centimes EUR/USD, unités XOF/JPY. Prix TTC. Les totaux des commandes sont calculés à partir des prix serveur, pas du navigateur.
- Authentification SPA par session Sanctum, cookie CSRF obtenu via `/sanctum/csrf-cookie`, header `X-XSRF-TOKEN` pour les mutations. Cookies sécurisés et HTTPS à configurer pour la production. Aucun token de session dans localStorage.

## Authentification et compte

| Méthode et chemin | Corps / règle |
| --- | --- |
| POST `/auth/register` | name ≤100, email ≤254, password 12–128, password_confirmation, locale fr/en ; is_admin ignoré ; crée une session et demande la vérification e-mail |
| POST `/auth/login` | email, password ; session renouvelée, erreurs génériques |
| POST `/auth/logout` | invalide la session |
| POST `/auth/forgot-password` | email ; réponse générique, lien temporaire envoyé si compte éligible |
| POST `/auth/reset-password` | email, token, password, password_confirmation ; invalide les sessions persistantes |
| POST `/auth/verification-notification` | session, renvoi limité |
| GET `/auth/verify/{id}/{hash}` | session du propriétaire et URL signée non expirée |
| GET `/me` | identité sans mot de passe ni remember_token |
| PATCH `/profile` | name, email, locale ; current_password exigé pour changer l’e-mail, qui devient non vérifié |
| PUT `/profile/password` | current_password, password, password_confirmation |
| POST `/profile/deletion-request` | current_password ; demande enregistrée pour traitement, aucune suppression immédiate ni promesse de délai légal |
| GET/POST `/addresses` | label, name, street, city, postal, country ISO alpha-2, is_default ; 20 maximum |
| PUT/DELETE `/addresses/{address}` | propriétaire uniquement ; une adresse par défaut maintenue |

Les limites d’authentification et des formulaires sont définies dans AppServiceProvider. Le rôle administrateur n’est pas accepté depuis les formulaires publics ; il doit être provisionné depuis un contexte serveur autorisé. Aucun compte administrateur de démonstration n’est créé par les seeders.

## Catalogue et sélections

| Méthode et chemin | Contrat |
| --- | --- |
| GET `/categories` | noms FR/EN, slug, ordre |
| GET `/products` | produits publiés ; q ≤120, category (id), min/max en unités mineures de référence, stock available/unavailable, sort newest/price-up/price-down, page, per_page |
| GET `/products/{slug}` | produit publié, variantes actives, photos et URL contrôlée |
| GET `/images/{image}` | image locale du produit publié, ou accès administrateur ; nosniff |
| PUT `/favorites/{product}` | la lecture de la liste se fait sur GET `/favorites` ; PUT ajoute une référence publiée |
| DELETE `/favorites/{product}` | retire uniquement la sélection du client |
| GET `/cart` | lignes du client avec variante et produit |
| PUT `/cart/items` | variant_id, quantité **absolue** 1–99 ; prix et rôle fournis sont ignorés ; stock/publication vérifiés |
| DELETE `/cart/items/{variant}` / DELETE `/cart` | retrait d’une ligne / vidage |

Le client fusionne ses sélections locales au point 12 en relisant les disponibilités ; les mutations sont explicites et ne remplacent jamais les prix serveur.

## Commandes et paiements

POST `/orders`, session **et e-mail vérifié** :

```json
{"address_id": 1, "idempotency_key": "UUID", "currency": "EUR"}
```

La devise est optionnelle (référence par défaut). Le service verrouille l’utilisateur et les variantes, photographie les articles/adresse, réserve le stock et calcule le total. Une même clé et le même contexte renvoient la commande existante ; réutilisation avec une autre adresse/devise : 409. Le panier reste intact ; une nouvelle clé crée une autre tentative et réserve à nouveau si le stock le permet.

- GET `/orders`, GET `/orders/{reference}` : client propriétaire, listes paginées et détails.
- POST `/orders/{reference}/cancel` : commande awaiting_payment sans session Stripe seulement ; libère le stock une seule fois.
- POST `/orders/{reference}/payment` : session Checkout hébergée, montant imposé et clé fournisseur déterministe. La réponse contient uniquement URL Stripe validée et référence. La création initiale doit laisser au moins 31 minutes avant expiration (contrainte du prestataire) ; sinon annuler la réservation sans session puis créer une nouvelle tentative. Les paramètres de paiement restent stables sur les reprises idempotentes.
- POST `/stripe/webhook` : **sans session ni CSRF**, signature HMAC vérifiée sur corps brut, fenêtre de 5 minutes, événements dédupliqués. Montant, devise et session liés vérifiés avant confirmation.
- Les retours du navigateur ne confirment aucun paiement. Paiement tardif d’une réservation déjà libérée : `payment_review`, sans expédition automatique.
- `commerce:expire-reservations` : libère les réservations sans session fournisseur. Une session fournisseur existante attend son événement signé d’expiration/échec ; elle n’est pas libérée aveuglément par une horloge locale.

Statuts : awaiting_payment → paid → preparing → shipped → delivered ; awaiting_payment → cancelled/expired ; paiement tardif → payment_review. Les délais de réserve sont indépendants des frais et délais de livraison.

## International, TTC et configuration

La cible fonctionnelle est tous pays et toutes devises. Le modèle valide les codes ISO locaux (`config/markets.php`, issus des données iso-codes du système) ; **cela ne signifie pas que chaque marché est déjà activé chez Stripe ou livrable**.

La vente est fermée par défaut. `commerce.enabled`, pays, validation fiscale TTC et livraison doivent être configurés. `countries: ["*"]` cible tous les codes pays valides ; le responsable doit valider les capacités commerciales/logistiques avant activation. Les règles actuelles de livraison sont un montant fixe et un seuil, pas un devis transporteur mondial ni un moteur fiscal international.

Les taux serveur sont des ratios rationnels datés, requis pour chaque devise différente de la référence et refusés après 24 h. Exemple de **forme**, à renseigner avec une valeur vérifiée :

```json
{"USD":{"numerator":11225,"denominator":10000,"updated_at":"date ISO vérifiée"}}
```

La conversion respecte les décimales ISO via Intl et arrondit les prix unitaires avant total des lignes. Les limites de montant protègent les opérations entières. `COMMERCE_PAYMENT_CURRENCIES` doit contenir uniquement les devises effectivement activées auprès du prestataire. Aucun taux réel, secret ou paiement de production n’est ajouté automatiquement.

## Administration

Toutes les routes `/admin/*` exigent une session vérifiée et `is_admin` serveur.

- GET `/admin/dashboard`, GET `/admin/{kind}` avec kind products/categories/stocks/orders/returns/messages/activity/customers ; q/status selon les listes, pagination 20.
- GET `/admin/products/{id}`, `/admin/customers/{id}`, `/admin/messages/{id}`, `/admin/returns/{id}` ; GET `/admin/orders/{reference}`.
- POST/PUT `/admin/categories[/{category}]` : slug, name.fr/en, position. DELETE exige replacement_id si des produits sont associés.
- POST/PUT `/admin/products[/{product}]` : catégorie, slug, marque, noms/descriptions FR/EN, specifications, status draft/published/archived. Publication exige une variante active et un visuel.
- POST/PUT `/admin/products/{product}/variants[/{variant}]` : sku, options, price, compare_at_price, currency, active. Stock modifié uniquement par son endpoint dédié.
- POST `/admin/variants/{variant}/stock` : stock absolu et reason ; verrou, mouvement et audit.
- POST `/admin/products/{product}/images` : fichier JPEG/PNG/WebP ≤8 Mo et dimensions ≤5000, alt.fr/en, position ; huit images maximum. DELETE `/admin/images/{image}` protégé, dernière image d’un produit publié conservée.
- PATCH `/admin/orders/{reference}` : status preparing/shipped/delivered ; carrier et tracking_number exigés pour expédition ; transitions séquentielles, notification après commit.
- PATCH `/admin/returns/{reference}` : status approved/rejected/received/refunded, reason, restock ; remboursement idempotent lié à la transaction. Le prestataire peut renvoyer un état pending : suivi par événements refund.created/updated/failed, sans annoncer de succès prématuré.
- PATCH `/admin/messages/{message}` : new/handled ; aucun e-mail libre envoyé à un tiers par cette route.
- GET/PUT `/admin/settings` : paramètres commerciaux autorisés seulement ; modification avec current_password, prix TTC imposés, audit. Secrets Stripe exclus.

## Retours, contact et newsletter

- GET `/returns`, POST `/returns` : order_item_id, quantity, reason. Propriétaire d’une commande livrée, délai configuré, quantité bornée et une demande par ligne. Délai de retour 0 par défaut : aucun droit commercial inventé.
- POST `/contact` : name, email, subject product/order/other, message ≤2000, locale ; réception en base, suivi administratif.
- POST `/newsletter` : email, locale ; confirmation explicite par lien temporaire, token stocké haché, réponse générique.
- GET `/newsletter/confirm/{token}` et POST `/newsletter/unsubscribe/{token}` ; le frontend de confirmation/désinscription reste à connecter au point 12.

## Exploitation et validation

```bash
docker compose up -d postgres
docker compose run --rm api vendor/bin/phpunit
docker compose run --rm api php artisan migrate --no-interaction
docker compose --profile jobs up -d worker scheduler
```

Les tests refusent toute base autre que `novashop_testing`. Ils utilisent les notifications et requêtes Stripe simulées, et un test de deux processus concurrents pour la dernière unité. La CI doit inclure pcntl pour ce test. Aucun appel de paiement réel n’est exécuté.

Avant ouverture : configurer SMTP/queue/scheduler et alertes, fournir les clés Stripe de test puis valider l’intégration réelle, contrôler les domaines/cookies/HTTPS, renseigner les règles locales de taxes/livraison/retours et organiser le traitement des demandes de suppression. Le rapprochement automatique (`commerce:reconcile-payments`, toutes les cinq minutes) interroge les sessions et remboursements en attente avec les clés serveur. Le traitement manuel des `payment_review`/`refund_failed`, l’intégration transporteur, les rapports fiscaux, les pièces jointes de retour et la modération des avis ne sont pas automatisés ici.
