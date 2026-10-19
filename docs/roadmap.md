# Plan de développement

Les fondations Laravel 13, PostgreSQL 17, client API Vue et CI sont configurées. Un prototype frontend Vue est disponible : interface responsive, catalogue fictif, recherche, filtres, tri, fiches produits, favoris et panier local. Les étapes ci-dessous concernent la réalisation de la plateforme complète, notamment sa connexion au backend. Chaque étape doit laisser une application vérifiable avant de commencer la suivante.

## 1. Initialisation

- [x] Choisir et verrouiller les versions Laravel, PHP et Vue ; fixer la version Node de la CI.
- [x] Initialiser `backend/` et `frontend/`, PostgreSQL et les exemples de configuration sans secrets réels.
- [x] Fournir les commandes d’installation, migrations, démarrage et tests dans le README.
- [x] Installer les tests, le formatage PHP et la configuration CI.
- [ ] Vérifier la première exécution distante de la CI après publication sur GitHub.

Livrable : un autre développeur peut démarrer les deux applications à partir du README.

## Frontend avant les modules métier

La séquence de finalisation frontend → API métier → connexion suit le [plan des pages et de la navigation](pages-and-navigation.md). Les lots métier ci-dessous restent le détail technique de réalisation.

- [x] Point 1 — Formaliser l’inventaire des pages, URL, liens accueil/en-tête/footer, accès et critères de finalisation.
- [ ] Point 2 — Finaliser l’en-tête et le footer selon les destinations disponibles, puis activer les autres liens avec leurs pages.

- [x] Installer Vue Router 4 et séparer les vues publiques du layout partagé.
- [x] Donner des URL à l’accueil, à la boutique, aux favoris et aux produits.
- [x] Synchroniser les filtres avec l’URL et afficher les pages introuvables.
- [x] Borner et normaliser les paramètres, renforcer la restauration du panier local et vérifier la navigation.
- [ ] Compléter le catalogue et les fiches produits selon le contrat API à définir.
- [ ] Développer les pages panier, checkout, espace client et administration, avant connexion aux modules backend correspondants.

## 2. Catalogue et authentification

- [ ] Modéliser les produits, catégories et images ; créer des données fictives.
- [ ] Développer catalogue, fiches, recherche, filtres et pagination.
- [ ] Implémenter les sessions, l’inscription, la récupération de mot de passe et les autorisations.

Livrable : catalogue consultable sur mobile et comptes isolés par leurs droits.

## 3. Panier et commandes

- [ ] Gérer panier visiteur, panier client et fusion à la connexion.
- [ ] Recalculer les montants côté serveur et figer les données de commande.
- [ ] Implémenter les réservations et tester les achats concurrents.

Livrable : création de commandes cohérentes sans survente ni double réservation.

## 4. Paiement Stripe

- [ ] Créer les sessions Checkout en mode test et gérer leur idempotence.
- [ ] Vérifier et traiter les webhooks avec reprise sur échec.
- [ ] Gérer expiration, paiements tardifs et rapprochement.
- [ ] Valider le parcours complet, du panier à la confirmation serveur.

Livrable : un paiement test confirmé met à jour une seule fois la commande et le stock.

## 5. Administration et suivi

- [ ] Gérer produits, catégories, stocks et images.
- [ ] Contrôler et historiser les transitions de livraison.
- [ ] Fournir historique client, suivi et tableau de bord administrateur.

Livrable : une commande payée peut être traitée et suivie jusqu’à la livraison.

## 6. Préparation de la publication

- [ ] Choisir devise, pays desservis, règles de taxes, livraison et retours.
- [ ] Vérifier accessibilité, sécurité, sauvegardes et restauration.
- [ ] Documenter hébergement, déploiement, supervision et reprise.
- [ ] Ajouter captures réelles et résultats de tests à la présentation.
- [ ] Choisir une licence et vérifier les droits sur les ressources avant publication publique.

Livrable : démonstration reproductible, documentation à jour et statut des fonctionnalités fidèle au code livré.
