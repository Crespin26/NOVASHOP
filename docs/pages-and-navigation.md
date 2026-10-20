# Plan des pages et de la navigation

Ce document est la référence de travail pour les pages, les URL et les destinations NovaShop. Il formalise le point 1 du plan de finalisation ; il ne déclare pas les pages futures comme implémentées.

## Périmètre et état de départ

- Une boutique unique, un catalogue, un espace client et une administration. Pas de comptes vendeurs ni de marketplace dans cette version.
- Ordre de réalisation : contrat des écrans et données attendues → frontend de démonstration → API métier → connexion et validation complète.
- Hypothèse de départ : produits physiques, compte requis pour commander, une devise principale. Le code de démonstration utilise EUR ; devise commerciale, variantes, pays, taxes, livraison et politique de retour restent à définir avant les modules concernés.
- Le catalogue, les prix, les avis, les stocks et les montants affichés sont fictifs. Une fonctionnalité frontend n’est considérée comme complète commercialement qu’après connexion et validation serveur.

État observé dans `frontend/src/router/index.js` : les routes publiques incluent désormais aussi connexion, inscription, récupération, réinitialisation explicative et espace compte de démonstration. Le panier dispose d’une page `/panier` et d’un tiroir partagé ; le checkout est un parcours de démonstration en trois étapes. Les autres destinations de ce document restent à créer. Les pages publiques existantes restent à enrichir selon leurs critères ci-dessous.

### Lecture des statuts et accès

- **Prototype** : route et vue disponibles avec données locales.
- **À créer** : aucune page dédiée actuellement.
- **Action** : interaction, sans obligation de créer une URL.
- **Public** : consultable sans session.
- **Client** : session requise ; données limitées au propriétaire de la ressource.
- **Administrateur** : session et autorisation serveur requises.

Les noms de routes ci-dessous sont des cibles stables pour les futurs liens nommés Vue Router. Ils ne doivent être utilisés dans le code qu’après déclaration effective de la route.

## Inventaire des pages

### Boutique et informations publiques

| ID | Page | Chemin | Nom de route cible | État | Contenu et sortie principale |
| --- | --- | --- | --- | --- | --- |
| PUB-01 | Accueil | `/` | `home` | Prototype | Sélections, univers, offres et nouveautés → catégorie, produit ou liste correspondante |
| PUB-02 | Boutique / recherche | `/boutique` | `catalog` | Prototype | Recherche, filtres, tri, pagination → produit |
| PUB-03 | Catégorie | `/categories/:slug` | `category` | Prototype | Introduction, sous-sélections et catalogue filtré → produit |
| PUB-04 | Promotions | `/promotions` | `promotions` | Prototype | Réductions actives, prix de référence et filtres → produit |
| PUB-05 | Nouveautés | `/nouveautes` | `new-arrivals` | Prototype | Produits classés selon leur date de publication → produit |
| PUB-06 | Produit | `/produits/:slug` | `product` | Prototype | Images, prix, description, disponibilité, options éventuelles → panier ou favoris |
| PUB-07 | Favoris | `/favoris` | `favorites` | Prototype | Liste sauvegardée, retrait, achat et état vide → produit ou boutique |
| PUB-08 | Panier | `/panier` | `cart` | Prototype | Articles, quantités, frais estimés et total → identification ou checkout |
| PUB-09 | À propos | `/a-propos` | `about` | À créer | Présentation de l’entreprise et engagements vérifiables → boutique ou contact |
| PUB-10 | Contact | `/contact` | `contact` | À créer | Coordonnées réelles, formulaire, confirmation et erreur d’envoi |
| PUB-11 | Aide / FAQ | `/aide` | `help` | À créer | Questions classées → livraison, retours, compte ou contact |
| PUB-12 | Livraison | `/livraison` | `delivery` | À créer | Zones, coûts, délais et conditions de gratuité → boutique ou contact |
| PUB-13 | Retours / remboursements | `/retours` | `returns-policy` | À créer | Conditions et procédure → commandes du client ou contact |
| PUB-14 | CGV | `/conditions-generales-de-vente` | `terms` | À créer | Contenu commercial validé pour l’activité |
| PUB-15 | Confidentialité | `/confidentialite` | `privacy` | À créer | Informations sur les traitements réellement utilisés |
| PUB-16 | Mentions légales | `/mentions-legales` | `legal` | À créer | Identification de l’entreprise et informations de publication |
| PUB-17 | Cookies | `/cookies` | `cookies` | À créer | Information ; ouverture des préférences si des traceurs optionnels sont intégrés |
| PUB-18 | Page introuvable | Chemin non reconnu | `not-found` | Prototype | Message sans reproduction de l’URL brute → boutique ou accueil |

Tous les écrans de cette table sont publics. `PUB-03`, `PUB-04` et `PUB-05` réutiliseront le composant de liste de `PUB-02`, avec leurs propres règles de sélection. Une catégorie ou un produit inconnu affiche un état introuvable ; une catégorie connue sans produits affiche un état vide.

### Authentification et espace client

| ID | Page | Chemin | Nom de route cible | Accès cible | État / contenu |
| --- | --- | --- | --- | --- | --- |
| AUTH-01 | Connexion | `/connexion` | `login` | Public, destinée au visiteur | Prototype public de démonstration ; connexion, lien inscription et mot de passe oublié |
| AUTH-02 | Inscription | `/inscription` | `register` | Public, destinée au visiteur | Prototype public de démonstration ; création du compte et informations nécessaires |
| AUTH-03 | Mot de passe oublié | `/mot-de-passe-oublie` | `forgot-password` | Public | Prototype public de démonstration ; réponse générique après demande de lien |
| AUTH-04 | Nouveau mot de passe | `/reinitialiser-mot-de-passe/:token` | `reset-password` | Lien temporaire validé par le serveur | Prototype public de démonstration ; token à usage unique et contexte minimal exigé par l’API |
| AUTH-05 | Vérification e-mail | `/verification-email` | `verify-email` | Client | Prototype public de démonstration ; état de vérification et renvoi limité ; le lien signé sera traité par Laravel |
| CLI-01 | Résumé du compte | `/compte` | `account` | Client | Prototype public de démonstration ; raccourcis profil, adresses et commandes |
| CLI-02 | Profil et sécurité | `/compte/profil` | `account-profile` | Client | Prototype public de démonstration ; profil, mot de passe et demande de suppression du compte |
| CLI-03 | Adresses | `/compte/adresses` | `account-addresses` | Client propriétaire | Prototype public de démonstration ; liste, création, modification, suppression et adresse par défaut |
| CLI-04 | Mes commandes | `/compte/commandes` | `account-orders` | Client propriétaire | Prototype public de démonstration ; historique, filtres et pagination |
| CLI-05 | Détail de commande | `/compte/commandes/:reference` | `account-order` | Client propriétaire | À créer ; instantanés des articles, adresse, montants et actions autorisées |
| CLI-06 | Suivi | `/compte/commandes/:reference/suivi` | `account-order-tracking` | Client propriétaire | À créer ; étapes, transporteur et numéro de suivi disponible |
| CLI-07 | Demande de retour | `/compte/commandes/:reference/retour` | `account-order-return` | Client propriétaire, commande admissible | À créer ; articles, motif, pièces utiles et confirmation |

La connexion terminée mène par défaut à `/compte`. Un retour au checkout peut être conservé par un état temporaire limité à une destination interne autorisée. Aucune URL externe fournie par un utilisateur ne sera utilisée comme destination de retour.

La déconnexion est une action serveur avec protection CSRF : fermer la session, invalider les données privées affichées, puis revenir à l’accueil. Le remplacement du compte connecté et la suppression de compte doivent également empêcher l’affichage de données du compte précédent.

Les formulaires d’adresse peuvent être des fenêtres accessibles dans `CLI-03`. Les moyens de paiement enregistrés, les avis clients publiables et les avantages de fidélité ne font pas partie de cette première version.

### Parcours de commande

| ID | Page / étape | Chemin | Nom de route cible | Accès cible | État / comportement |
| --- | --- | --- | --- | --- | --- |
| CMD-01 | Checkout | `/commande` | `checkout` | Client, panier validé | À créer ; adresse → livraison → récapitulatif, dans une page à étapes |
| CMD-02 | Paiement hébergé | URL fournie par Stripe au serveur | Pas de route Vue | Session Checkout liée à une commande autorisée | À intégrer ; redirection provenant de la réponse serveur uniquement |
| CMD-03 | Retour de paiement | `/commande/retour` | `checkout-return` | Client, tentative liée à sa commande | À créer ; vérification auprès de l’API, états en attente, payé ou échoué |
| CMD-04 | Confirmation | `/commande/confirmation/:reference` | `checkout-confirmation` | Client propriétaire | À créer ; état confirmé par le serveur, récapitulatif et accès au suivi |
| CMD-05 | Paiement interrompu | `/commande/paiement-interrompu` | `checkout-interrupted` | Client, contexte de tentative serveur | À créer ; état connu, retour au panier et nouvelle tentative si autorisée |

Un lien direct ou un rechargement ne déclenche jamais de débit, création de commande ou remboursement. Le bouton de validation utilise une opération serveur idempotente. Un panier vide renvoie vers la page panier avec une explication. Un changement de prix ou de disponibilité est présenté avant confirmation.

La page de retour n’atteste pas un paiement : elle consulte le serveur. Les identifiants de commande sont des références, jamais des autorisations d’accès. Une fermeture du navigateur ou un échec réseau n’empêche pas le serveur de traiter les événements Stripe.

### Administration

Toutes ces pages sont **à créer** et nécessiteront une autorisation administrateur côté serveur. Aucun rôle fourni par une URL ou le stockage du navigateur ne fera autorité.

| ID | Page | Chemin | Nom de route cible | Actions principales |
| --- | --- | --- | --- | --- |
| ADM-01 | Tableau de bord | `/admin` | `admin-dashboard` | Indicateurs → commandes, stocks ou produits concernés |
| ADM-02 | Produits | `/admin/produits` | `admin-products` | Rechercher, filtrer, publier, archiver et ouvrir l’édition |
| ADM-03 | Nouveau produit | `/admin/produits/nouveau` | `admin-product-create` | Créer informations, prix, catégorie, images et options retenues |
| ADM-04 | Modifier produit | `/admin/produits/:id/modifier` | `admin-product-edit` | Modifier avec validation et avertissement de changements non enregistrés |
| ADM-05 | Catégories | `/admin/categories` | `admin-categories` | Créer, modifier, classer et réaffecter avant suppression |
| ADM-06 | Stocks | `/admin/stocks` | `admin-stock` | Ajustements motivés, alertes et historique |
| ADM-07 | Commandes | `/admin/commandes` | `admin-orders` | Rechercher, filtrer et ouvrir une commande |
| ADM-08 | Détail de commande | `/admin/commandes/:reference` | `admin-order` | Préparer, expédier, annuler ou rembourser selon l’état et les droits |
| ADM-09 | Clients | `/admin/clients` | `admin-customers` | Rechercher et accéder aux informations nécessaires au service client |
| ADM-10 | Fiche client | `/admin/clients/:id` | `admin-customer` | Profil utile et historique autorisé |
| ADM-11 | Retours | `/admin/retours` | `admin-returns` | Liste, filtres et ouverture du traitement |
| ADM-12 | Détail de retour | `/admin/retours/:reference` | `admin-return` | Décision, réception et remboursement autorisé |
| ADM-13 | Messages | `/admin/messages` | `admin-messages` | Liste et ouverture d’un message |
| ADM-14 | Détail de message | `/admin/messages/:id` | `admin-message` | Lecture et suivi du traitement ; aucun envoi réel sans action explicite |
| ADM-15 | Paramètres | `/admin/parametres` | `admin-settings` | Paramètres commerciaux autorisés ; secrets d’infrastructure hors interface |
| ADM-16 | Journal d’activité | `/admin/activite` | `admin-activity` | Consultation des actions administratives importantes |

Les catégories utilisent une liste avec formulaires accessibles ; pas de nouvelle URL obligatoire pour chaque formulaire. Les opérations destructives nécessitent une confirmation contextualisée. L’archivage conserve les instantanés des commandes existantes.

L’administration aura un layout distinct : navigation latérale, compte courant, déconnexion et lien « Voir la boutique » vers `/`. Les aperçus frontend resteront clairement fictifs, sans accès aux données réelles avant activation des protections serveur.

## Destinations depuis l’accueil, de haut en bas

### Bandeau et en-tête

| Élément | Destination / action cible | État après le point 2 / suite |
| --- | --- | --- |
| Information livraison | `delivery` | Texte informatif ; ajouter un lien si présenté comme action |
| Annonce commerciale | `promotions` ou catégorie nommée dans l’annonce | Ne pas afficher d’offre sans règle et contenu correspondants |
| Langue / devise | Sélecteur uniquement si plusieurs options fonctionnent | FR/EUR est une indication, pas un sélecteur ; ne pas ajouter de faux contrôle |
| Logo | `home` | Liens harmonisés dans l’en-tête et le footer |
| Recherche soumise | `catalog`, paramètre `q`, filtres applicables | Disponible ; conserver validation et longueur maximale |
| Suggestion produit | `product`, slug connu | Suggestions à développer si retenues |
| Suggestion catégorie | `category`, slug connu | Suggestions à développer si retenues |
| Compte visiteur | `login` | Remplacer la fenêtre explicative une fois la page disponible |
| Compte connecté | `account` | À activer sur la base de la session serveur |
| Favoris | `favorites` | Liens disponibles dans l’en-tête, le menu mobile et le footer |
| Panier | Ouvrir le tiroir partagé | Tiroir dans l’en-tête ; lien « Voir mon panier » vers `/panier`, également accessible dans le footer |
| Menu mobile | Ouvrir / fermer les mêmes destinations que sur ordinateur | Menu mobile complet sur les destinations disponibles ; clavier et fermeture vérifiés |

### Navigation principale et catégories

| Élément | Destination cible | Règle |
| --- | --- | --- |
| Accueil | `home` | État actif sur l’accueil |
| La boutique | `catalog` | Catalogue complet |
| Bonnes affaires | `promotions` | Liste dédiée disponible depuis le point 3 ; le lien au simple tri croissant a été retiré. Une réduction et un bas prix sont deux critères différents |
| Nouveautés | `new-arrivals` | Liste dédiée disponible depuis le point 3, selon la date de publication fictive |
| Tout découvrir | `catalog` | Aucun filtre de catégorie |
| Catégorie, menu ou colonne latérale | `category` | Même slug et destination dans les deux menus |
| Inspiration du moment | Catégorie réellement présentée | Pour le premier contenu : `category`, slug `maison-deco` |

Correspondance initiale : High-tech → `high-tech` ; Mode & accessoires → `mode-accessoires` ; Maison & déco → `maison-deco` ; Beauté & bien-être → `beaute-bien-etre` ; Sport & plein air → `sport-plein-air`. « Tout découvrir » n’est pas une catégorie métier.

### Sections de l’accueil

| Élément | Destination / action cible |
| --- | --- |
| Bouton bannière audio | Catégorie `high-tech` |
| Bouton bannière montre | Catégorie `mode-accessoires` |
| Bouton bannière intérieur | Catégorie `maison-deco` |
| Visuel ou libellé d’un produit explicitement cliquable | `product`, slug du produit présenté |
| Indicateurs du carrousel | Changer de bannière, sans changer de page |
| « Voir les offres » | `promotions` |
| Image ou titre d’une carte produit | `product` ; vrai lien ouvrable dans un nouvel onglet |
| Cœur | Ajouter / retirer des favoris, sans navigation ; annoncer le résultat |
| Bouton ajout panier | Ajouter un article valide ; si une option est indispensable, ouvrir sa sélection avant ajout |
| Carte univers | `category`, slug correspondant |
| « Tout explorer » | `catalog` |
| Bloc éditorial « Trouver mon prochain coup de cœur » | `catalog` |
| « Voir les nouveautés » | `new-arrivals` ; harmoniser le libellé actuel « Voir la sélection » |
| Bannière audio | Catégorie `high-tech` ; un futur filtre audio exigera une catégorie ou un attribut réel |
| Bannière mode | Catégorie `mode-accessoires` |
| Services livraison / retours / aide | `delivery`, `returns-policy`, `help` si rendus cliquables |
| Valeurs de marque | Texte informatif ; pas de lien artificiel obligatoire |

Le bouton générique d’une collection mène à sa liste ; le lien d’un produit nommé mène à sa fiche. Chaque réduction, compteur, avis et engagement devra provenir d’une donnée vérifiable avant commercialisation.

### Newsletter et footer

| Élément | Destination / action cible |
| --- | --- |
| Newsletter | Valider et envoyer au service prévu ; succès, doublon et erreur ; rester explicitement simulée avant connexion |
| Information confidentialité du formulaire | `privacy` |
| Logo footer | `home` |
| Explorer | `catalog`, catégories, `promotions`, `new-arrivals` |
| NovaShop | `about`, `contact` |
| Mon espace | `account` ou `login` selon la session, `account-orders`, `favorites`, `cart` |
| « Suivre une commande » | `account-orders`, puis suivi de la commande choisie ; supprimer le toast provisoire |
| Aide | `help`, `delivery`, `returns-policy`, `contact` |
| Informations légales | `terms`, `privacy`, `legal`, `cookies` |
| Préférences cookies | Ouvrir les préférences uniquement si des catégories optionnelles existent |
| Réseaux sociaux | Comptes officiels configurés ; masquer toute destination absente |
| Logos de paiement | Information sur les moyens effectivement acceptés ; pas de faux lien de paiement |

Un visiteur cliquant sur les commandes sera dirigé vers la connexion avec une intention interne autorisée. Le footer n’exposera aucun numéro de commande ni donnée personnelle.

## Navigation entre les pages

| Départ | Action | Arrivée / résultat |
| --- | --- | --- |
| Liste boutique, catégorie, promotions ou nouveautés | Ouvrir un produit | Fiche produit ; le retour navigateur retrouve filtres, page et défilement |
| Produit | Ajouter au panier | Confirmation et compteur actualisé ; rester sur le produit |
| Tiroir panier | « Voir mon panier » | `/panier` ; fermer le tiroir |
| Tiroir / page panier | « Commander » | Connexion si nécessaire, puis `/commande` après validation du contexte |
| Panier vide | « Découvrir la boutique » | `/boutique` |
| Checkout | Modifier le panier | `/panier` ; recalcul avant reprise |
| Checkout valide | Payer | Session hébergée Stripe créée par le serveur |
| Retour Stripe | Vérifier le résultat | En attente, confirmation ou état d’échec selon l’API |
| Confirmation | « Voir ma commande » | Détail personnel de commande |
| Détail de commande | « Suivre » | Suivi de cette commande |
| Détail de commande admissible | « Demander un retour » | Formulaire lié à cette commande |
| Retour envoyé | Confirmation | Détail de commande avec état de la demande |
| Espace client | Navigation latérale | Profil, adresses, commandes ou favoris |
| FAQ | Question non résolue | `/contact` |
| Page inconnue | Explorer / accueil | `/boutique` ou `/` |
| Administration | Liste → ligne → action autorisée | Détail ou formulaire de la ressource concernée |
| Administration | « Voir la boutique » | `/` |

## Règles de liens, d’URL et de sécurité

### Liens et actions

- Utiliser des liens nommés pour naviguer : destinations prévisibles, ouverture dans un nouvel onglet et état courant accessible.
- Utiliser des boutons pour les actions : panier, favoris, menus, soumission, déconnexion et opérations administratives.
- Aucun `href="#"`, URL vide, lien externe factice ou bouton silencieux. Une page non construite n’est pas présentée comme disponible. Les aperçus explicatifs actuels restent temporaires et identifiés.
- Au point 2, ajouter les destinations à mesure que leurs pages existent ; ne pas annoncer des routes absentes. Le plan ne justifie pas de créer de faux écrans terminés.
- Un nouveau lien externe provient d’une configuration maîtrisée, utilise HTTPS et, s’il ouvre un onglet, `rel="noopener noreferrer"`. Ne pas consommer une URL de redirection libre depuis une query.
- Après navigation : titre de document cohérent, focus accessible et fermeture des tiroirs/menus. Retour/suivant et liens directs doivent fonctionner.

### Paramètres de navigation

| Paramètre | Périmètre | Règle cible | État |
| --- | --- | --- | --- |
| `q` | Boutique et favoris | Texte scalaire, caractères de contrôle retirés, 120 caractères maximum, affiché comme texte | Présent |
| `category` | Boutique et favoris | Slug connu ; la page catégorie utilise le slug du chemin comme référence | Présent sur les listes ; catégorie du chemin prioritaire sur une page dédiée |
| `sort` | Listes compatibles | Liste autorisée ; ne pas utiliser une valeur brute comme expression SQL | `featured`, `price-up`, `price-down` présents |
| `page` | Listes paginées | Entier positif, borné par les résultats ; retour à la page 1 après changement de filtre | Présent : 8 produits par page, retour à 1 après filtre et correction des pages excessives |
| Prix / disponibilité | Listes catalogue | Montants dans l’unité définie par le contrat, bornes cohérentes, disponibilité autorisée | Présent : `min`/`max` en centimes bornés, `stock` parmi deux valeurs autorisées |
| `:slug` | Produit / catégorie | Format accepté et existence vérifiée ; absent → introuvable | Produit et catégorie présents |
| `:id`, `:reference` | Compte / administration | Identifiant validé, puis autorisation serveur ; connaître l’URL n’accorde aucun droit | À implémenter |

La normalisation actuelle conserve `q`, `category`, `sort`, `min`, `max`, `stock` et `page` sur les listes, plus l’ancre `#main`. Sur une page catégorie, `category` est retiré au profit du slug du chemin. Avant l’ajout d’une nouvelle route, déclarer et tester ses paramètres autorisés dans `router/policy.js`. Les filtres des catégories dédiées sont intégrés à cette politique depuis le point 4.

Les liens temporaires d’authentification et le retour Stripe nécessiteront leur propre contrat avant activation : paramètres minimaux, validation serveur, pas d’analytics tiers sur les écrans sensibles et retrait des valeurs devenues inutiles. Ne pas appliquer aveuglément la politique publique actuelle à ces futurs liens, au risque de supprimer un token attendu. Ne pas placer de mot de passe, adresse, numéro de carte ou secret de session dans une URL ; la normalisation client n’efface pas les journaux déjà produits.

### Contrôle des accès et transitions

| Situation | Comportement cible |
| --- | --- |
| Visiteur ouvre une page client | Proposer la connexion ; API privée en 401, sans données personnelles |
| Client ouvre une ressource d’un autre client | Refus serveur selon la politique 403/404 ; aucun détail tiers |
| Client sans droit ouvre l’administration | Refus serveur ; écran d’accès refusé, sans chargement de données administratives |
| Session expire | Effacer les données privées en mémoire, expliquer et revenir à la connexion si nécessaire |
| Lien de réinitialisation invalide / expiré | Explication et nouvelle demande ; aucune confirmation d’existence de compte |
| Produit / catégorie absent | État introuvable et retour aux listes |
| Panier / favoris / commandes vides | État vide utile et action appropriée |
| Erreur réseau | Message, réessai et conservation des saisies non sensibles lorsque possible |
| Erreur API | Respecter le statut HTTP ; jamais transformer une erreur API en page HTML frontend |
| Refus ou interruption de paiement | État serveur, aucune confirmation de paiement locale ; réessai idempotent si permis |

Les gardes Vue faciliteront la navigation. Laravel restera responsable des autorisations, validations, montants, réservations de stock et confirmations de paiement. Aucun rôle ou indicateur de connexion du localStorage ne sera utilisé comme preuve d’accès. Les aperçus frontend privés ne seront pas exposés avec des données réelles avant ces contrôles.

Les écrans d’accès refusé, d’expiration et d’erreur peuvent être des composants de la page concernée ; ils ne nécessitent pas une URL publique supplémentaire. La page de maintenance sera traitée avec l’infrastructure. Le statut HTTP réel des pages introuvables sera vérifié avec l’hébergement, au-delà du simple affichage d’une 404 dans la SPA.

## Critères de finalisation d’une page

Pour chaque ID de l’inventaire :

- [ ] Présentation cohérente sur mobile, tablette et ordinateur.
- [ ] Chaque lien et bouton possède une destination ou action conforme à son libellé.
- [ ] URL directe, rechargement, historique et état actif vérifiés.
- [ ] Chargement, vide, erreur, succès et indisponibilité prévus selon le parcours.
- [ ] Navigation clavier, focus, libellés et messages accessibles vérifiés.
- [ ] Formulaires validés, erreurs lisibles et doubles soumissions gérées.
- [ ] Aucune donnée personnelle ou secret exposé sans nécessité dans URL, stockage ou journaux ; exceptions limitées aux liens temporaires explicitement prévus et validés côté serveur.
- [ ] Contrôles serveur et tests d’accès ajoutés pour toute fonctionnalité connectée sensible.
- [ ] Prix, frais et stocks recalculés côté serveur avant une commande réelle.
- [ ] Données de démonstration clairement distinguées ; contenus commerciaux validés avant publication.
- [ ] Tests utiles réussis et documentation de statut actualisée.

## Ordre de réalisation et sortie de chaque point

| Point | Lot | Livrable vérifiable |
| --- | --- | --- |
| 1 | Plan des pages et navigation | Ce document : inventaire, routes, destinations, accès, écarts et critères |
| 2 | En-tête et footer | Layouts, menus et liens sémantiques ; destinations disponibles correctement reliées, aucune promesse de page absente |
| 3 | Accueil | Chaque sélection et action mène à son contenu ; aucune offre ou nouveauté confondue avec un simple tri |
| 4 | Boutique et catégories | Listes dédiées, promotions, nouveautés, filtres, pagination et états |
| 5 | Produits et favoris | Fiche enrichie, options retenues, suggestions pertinentes et favoris complets |
| 6 | Panier | Page et tiroir cohérents ; transitions vers le checkout préparées |
| 7 | Authentification et compte frontend | Formulaires et vues de démonstration ; accès réel reporté aux API sécurisées |
| 8 | Checkout et suivi frontend | Étapes et états illustrés, sans paiement ni commande réels |
| 9 | Informations et assistance | FAQ, contact, livraison, retours et contenus légaux validés avant ouverture |
| 10 | Administration frontend | Écrans distincts sur données fictives, actions sensibles clairement simulées |
| 11 | Backend métier | Contrats API, données, authentification, autorisations, stocks, commandes, Stripe et notifications testés |
| 12 | Connexion des modules | Remplacement des simulations par les API ; contrôles de sécurité et tests par parcours |
| 13 | Validation finale | Revue de navigation, sécurité, accessibilité, tests complets et déploiement |

Le point 2 est une première passe de navigation globale. Les liens qui dépendent des points suivants seront activés avec leurs pages, puis une revue intégrale en point 13 vérifiera l’absence de destination provisoire. Définir les contrats de données de chaque écran avant de finaliser ses formulaires frontend évitera des divergences avec les API.

Les points 1 à 8 sont réalisés pour le périmètre frontend disponible. L’en-tête et le footer utilisent une configuration de navigation partagée, des liens réels, des rubriques « Bientôt » non cliquables et des actions panier/compte explicitement limitées à la démonstration. Le toast provisoire de suivi a été retiré, les logos de paiement non connectés aussi. Le menu mobile, les panneaux catégories et le focus des fenêtres ont été vérifiés.

Les catégories sont maintenant reliées à leurs pages dédiées, y compris depuis l’accueil, l’en-tête, le footer et les fiches produit. Les pages assistance et informations légales ne sont pas encore implémentées. Leur activation reste suivie avec les lots concernés ; cela ne bloque pas la première passe du point 2.

Le point 3 relie l’accueil à des sélections cohérentes, aux produits des bannières et aux listes promotions/nouveautés désormais disponibles. Les prix et réductions proviennent du même catalogue de démonstration ; les nouveautés reposent sur ses dates de publication fictives.

Le point 4 ajoute prix, disponibilité et pagination aux listes, ainsi que les pages catégorie dédiées et leurs états introuvables. Tous ces éléments reposent encore sur des données locales fictives.

Le point 5 enrichit chaque fiche avec un texte propre à la référence, un visuel agrandissable au clavier, un état d’échec de chargement, des informations dépliables et des suggestions de la même catégorie (disponibles en premier). Le changement de produit réinitialise quantité et agrandissement. Les références sont uniques : aucun sélecteur de variante fictif n’est proposé. Les images, avis, stocks et prix restent illustratifs ; les caractéristiques, garanties et conditions commerciales devront être validées avant une vente réelle.

Les favoris conservent leurs filtres, tris et pagination. Un récapitulatif indique le total hors livraison pour un exemplaire par référence et le nombre de produits indisponibles. La liste peut être vidée après confirmation explicite ; seuls les identifiants connus restent stockés localement. L’ajout au panier passe toujours par le produit canonique et les limites de stock existantes. Ces contrôles frontend devront être reproduits et imposés par les API.

Le point 6 ajoute `/panier`, ses liens depuis le footer et le tiroir, les lignes modifiables, le retrait individuel et la suppression globale avec confirmation. Page et tiroir partagent le même état, les mêmes limites de stock et le calcul centralisé en centimes (livraison fictive à 4,90 €, offerte dès 100 €, aucun frais pour un panier vide). La restauration locale ignore les prix fournis par le navigateur et utilise le catalogue canonique ; aucune donnée personnelle ni bancaire n’est enregistrée. L’action de commande ouvre encore une fenêtre explicative, sans créer de commande ni collecter de paiement.

Le point 7 ajoute les formulaires `/connexion`, `/inscription`, `/mot-de-passe-oublie`, l’état explicatif `/reinitialiser-mot-de-passe/:token` et les vues publiques de démonstration `/compte`, `/compte/profil`, `/compte/adresses`, `/compte/commandes`, `/verification-email`. Le lien compte de l’en-tête conduit à la connexion ; le footer permet de visiter l’aperçu directement. Les formulaires valident localement les champs, puis effacent les données : aucune session, aucun compte, aucun e-mail envoyé. La récupération utilise une réponse générique ; aucun token n’est validé côté client. La réinitialisation réelle et le renvoi de vérification restent désactivés en attente du serveur.

Profil et adresses fictives sont modifiables en mémoire seulement ; création, édition, retrait et choix d’une adresse par défaut sont limités à cet aperçu (5 adresses maximum). L’historique est vide ; détails et suivi seront traités au point 8. Aucun stockage de mot de passe ni donnée personnelle, aucune pseudo-authentification dans localStorage. Les règles serveur de session Sanctum, CSRF, autorisations par propriétaire, limitation des tentatives, vérification e-mail et jetons temporaires seront implémentées au point 11. Ces écrans publics ne constituent pas une protection d’accès.

Le point 8 ajoute `/commande` : coordonnées fictives validées, livraison standard simulée et récapitulatif avec reconnaissance explicite de la simulation. Un panier vide affiche une invitation à revenir au panier ; une modification du panier réinitialise les étapes. La confirmation repose sur un instantané canonique des articles et des montants, jamais sur les prix fournis par le stockage local. Les coordonnées ne sont ni transmises ni persistées ; elles sont effacées à la validation ou au départ de la page. Le panier reste intact.

Les simulations (20 maximum en mémoire) sont consultables dans `/compte/commandes`, avec détail, suivi illustratif et explication des retours. Confirmation et références inconnues ou perdues au rechargement affichent un état indisponible. Les routes de retour de paiement et de paiement interrompu ne déclenchent aucune mutation et ne confirment jamais un paiement à partir de l’URL. Aucun champ bancaire, appel Stripe, débit, e-mail ni expédition réelle. L’authentification, l’idempotence, les autorisations par propriétaire, le recalcul serveur et la vérification Stripe restent à implémenter aux points 11 et 12.

Prochain travail : point 9, informations et assistance.
