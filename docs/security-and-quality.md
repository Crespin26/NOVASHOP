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
