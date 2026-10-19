# Sécurité et qualité prévues

Ce document définit les exigences de la plateforme complète ; il ne constitue pas un audit. Les fondations fournissent déjà des sessions Sanctum préparées, des réponses JSON, une limitation de requêtes et des tests PostgreSQL. Les contrôles métier seront ajoutés avec chaque module.

## Sécurité

- Hachage des mots de passe via Laravel, renouvellement de session après connexion et invalidation à la déconnexion.
- Cookies sécurisés en production, HTTPS, protection CSRF et limitation des tentatives de connexion et de récupération du mot de passe.
- Autorisations côté serveur pour chaque ressource ; le rôle administrateur ne peut pas être fourni librement à l’inscription.
- Validation serveur des entrées, requêtes paramétrées, échappement de l’affichage et restriction des champs modifiables.
- Téléversement limité aux formats d’image autorisés avec contrôle du contenu, de la taille et du nom généré ; aucun fichier téléversé ne peut être exécuté.
- Paiement sur Stripe Checkout : aucune donnée de carte stockée par NovaShop.
- Signature des webhooks vérifiée sur le corps brut, déduplication par identifiant d’événement et contrôle de la commande, du montant, de la devise et de l’état de paiement.
- Événements Stripe traités sans dépendre de leur ordre d’arrivée. Un événement déjà reçu mais non traité reste réessayable.
- Journaux sans secrets, données de carte ni adresse complète ; accès aux données clients limité et politique de conservation à définir avant production.
- Sauvegardes chiffrées et restauration vérifiée avant ouverture du service.

## Routage public et stockage local

Mesures présentes dans cette étape :

- Routes publiques explicitement déclarées et vues chargées depuis une liste statique ; aucune URL utilisateur ne détermine un import de code.
- Paramètres limités à `q`, `category` et `sort` pour boutique/favoris : valeurs scalaires, longueur de recherche bornée et listes autorisées pour les autres filtres. Les paramètres inconnus, dont `redirect`, `token` et `role`, ne sont pas consommés et sont retirés de l’URL par le routeur.
- Produit résolu uniquement depuis un slug connu. Les chemins inconnus, y compris les futures zones privées, affichent la page introuvable.
- Texte issu des URL affiché avec l’interpolation Vue, sans `v-html` ni compilation de contenu utilisateur. Le navigateur vérifié n’exécute pas le HTML injecté dans une recherche. Voir les [recommandations de sécurité Vue](https://vuejs.org/guide/best-practices/security).
- Politique de référent `strict-origin-when-cross-origin` dans le document HTML.
- Panier local limité à des références et quantités ; à la restauration, les prix et stocks fournis par ce stockage sont ignorés. Entrées inconnues, quantités invalides et données corrompues sont écartées ; doublons regroupés et quantités plafonnées au stock fictif. Aucun jeton d’authentification, mot de passe ou détail de carte n’est stocké par cette logique.

Ces mesures frontend améliorent la robustesse de la démonstration. Elles ne créent pas de contrôle d’accès serveur ni de garantie de prix ou de disponibilité. Les futurs endpoints devront authentifier la session, contrôler les droits sur chaque ressource, recalculer les montants et gérer le stock transactionnellement. Les éventuels gardes Vue seront uniquement une aide de navigation ; aucun indicateur `isAdmin` ou `isLoggedIn` en stockage local ne devra faire autorité.

Ne pas placer de données personnelles, secrets ou informations de paiement dans les URL. Leur retrait côté client ne peut pas effacer une URL déjà reçue par un serveur ou enregistrée dans des journaux. Les politiques d’en-têtes HTTP (dont CSP), HTTPS et cookies de production seront configurées et testées avec l’hébergement et l’authentification.

## Stratégie de tests

| Niveau | Cas prioritaires |
| --- | --- |
| Backend | Validation, autorisations, calcul des totaux, transitions d’état et instantanés |
| Intégration base | Réservations concurrentes, transactions, expiration et absence de stock négatif |
| Intégration Stripe | Signature invalide, doublon, réessai après échec, événements désordonnés, montant ou devise incohérents |
| Frontend | Quantités, filtres, erreurs API, navigation et états de chargement |
| Bout en bout | Inscription, recherche, panier, checkout en mode test, suivi client et gestion administrateur |
| Sécurité | Accès à une commande tierce, élévation de rôle, requêtes non authentifiées et téléversement invalide |

Les tests de concurrence doivent utiliser le moteur de base cible avec plusieurs connexions. Un test transactionnel isolé en SQLite ne suffit pas à prouver le comportement des verrous PostgreSQL.

Les tests automatiques utilisent des doubles pour les appels Stripe courants. Une validation séparée en mode test Stripe vérifie le parcours hébergé et les webhooks avant livraison. Aucun paiement réel n’est nécessaire à la CI.

## CI/CD cible

Le fichier `.github/workflows/ci.yml` configure les installations à partir des fichiers de verrouillage, la validation Composer, les tests PHPUnit sur PostgreSQL 17, les tests frontend et la compilation Vue. Son exécution distante reste à vérifier après publication sur GitHub. Laravel Pint est disponible via `make format` ; les contrôles de format et l’analyse statique pourront être intégrés au pipeline avec les prochains modules.

Le déploiement devra prévoir migrations compatibles, exécution des tâches et du planificateur, contrôle de santé et procédure de retour arrière. La disponibilité, les erreurs de paiement et les échecs de tâches seront surveillés. Les commandes de déploiement restent à documenter après le choix de l’hébergement.
