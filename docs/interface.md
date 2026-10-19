# Interface NovaShop

Le [plan des pages et de la navigation](pages-and-navigation.md) définit les destinations finales, les accès et l’ordre de finalisation.

Le prototype Vue se trouve dans `frontend/`. Il reprend la structure des références fournies : en-tête, recherche, navigation par catégories, bannière de collection, offres, univers, nouveautés et pied de page.

## Direction visuelle

Une palette bleu nuit, corail et vert sauge, des titres Manrope et du texte DM Sans créent une identité distincte. Les photographies occupent une place importante ; les interactions ajoutent des zooms doux, des transitions, des favoris et des confirmations. Le mouvement est réduit si le système le demande.

La grille affiche quatre produits sur grand écran et deux sur mobile. La navigation et la bannière sont adaptées aux petits écrans. Les produits disposent désormais de pages dédiées. Le panier et les aperçus de compte/checkout proposent une fermeture au clavier, un confinement du focus et des libellés accessibles. Les liens produits peuvent être ouverts dans un nouvel onglet.

## Parcours disponibles

- Accueil : trois sélections de bannière contrôlées manuellement, univers, produits et collections.
- Boutique : recherche par nom ou marque, catégorie et tri par prix combinables.
- Fiche produit : aperçu, quantité, prix, stock illustratif et ajout au panier.
- Favoris : sélection conservée dans le navigateur.
- Panier : modification des quantités, suppression, sous-total et livraison estimée (4,90 €, offerte dès 100 €).
- Compte et checkout : fenêtres explicatives des intégrations à venir.
- Newsletter : validation d’adresse et confirmation simulée, sans envoi.

Les données ne représentent pas des offres commerciales réelles. Les avis sont illustratifs. Le prototype n’effectue ni authentification, ni paiement, ni réservation de stock serveur. La monnaie EUR est choisie pour la démonstration et reste à configurer pour la plateforme finale.

## Connexion backend

Le client `frontend/src/api.js` centralise les requêtes JSON, les erreurs, les délais d’attente et la préparation CSRF Sanctum. Vite transmet `/api` et `/sanctum` vers Laravel. Le statut de connexion API/PostgreSQL est visible uniquement en développement ; le catalogue métier reste local.

## Évolution

Remplacer les données de `frontend/src/shop.js` par les réponses de l’API Laravel, puis connecter authentification, commandes et Stripe. Revalider les prix et stocks côté serveur. Vue Router 4 fournit les pages publiques, avec chargement différé des vues. La recherche (`q`, 120 caractères maximum), la catégorie (`category`, slug autorisé) et le tri (`sort`) sont partagés via l’URL de la boutique, des favoris, des promotions ou des nouveautés. Le retour/suivant rétablit ces filtres. Les autres paramètres sont supprimés par le routeur. Les liens internes utilisent des destinations connues, sans paramètre de redirection externe.

Les images Unsplash et les polices Google sont chargées à distance. Avant publication commerciale, choisir et vérifier les droits des ressources finales, puis héberger les actifs validés localement.

## Routes et hébergement

| URL | Vue |
| --- | --- |
| `/` | Accueil |
| `/boutique` | Catalogue de démonstration |
| `/promotions` | Produits à prix réduit dans la démonstration |
| `/nouveautes` | Publications récentes du catalogue de démonstration |
| `/favoris` | Favoris locaux |
| `/produits/:slug` | Produit connu ou message produit introuvable |
| Tout autre chemin | Page introuvable |

Vite et son aperçu de production permettent le rechargement des liens directs. En production, le serveur devra renvoyer `index.html` pour les routes frontend, tout en conservant `/api/*` et `/sanctum/*` vers Laravel et les fichiers statiques manquants en erreur. Le fallback ne doit jamais remplacer une erreur API par du HTML. Une 404 affichée par une SPA statique peut avoir un statut HTTP 200 ; un véritable statut HTTP 404 devra être traité dans la stratégie d’hébergement/SSR si nécessaire.

Le panier reste un tiroir commun, et les espaces compte/checkout des aperçus. Leurs pages complètes seront développées dans les prochaines étapes.

Référence : [Vue Router — mode historique](https://router.vuejs.org/guide/essentials/history-mode.html).

## En-tête et footer — point 2

Les composants `StoreHeader.vue` et `StoreFooter.vue`, dans `frontend/src/components/layout/`, partagent les destinations de `frontend/src/navigation.js`. `NavigationItem.vue` distingue liens, actions et rubriques en préparation.

- Accueil, boutique, favoris et catégories du catalogue utilisent des liens internes connus. Les catégories mènent provisoirement au catalogue filtré ; les pages catégorie dédiées seront reliées lorsqu’elles existeront.
- Au point 3, promotions et nouveautés ont reçu leurs listes dédiées ; les liens sont désormais actifs dans l’en-tête et le footer. Les informations, pages légales et commandes absentes sont également indiquées sans faux lien.
- Le compte est un aperçu explicitement indiqué. Le panier reste un tiroir accessible depuis l’en-tête, le menu mobile et le footer ; ses produits renvoient à leur fiche. La page panier sera ajoutée au point 6.
- Les catégories sur ordinateur forment un panneau de navigation. Le menu mobile propose navigation principale, catégories, favoris, compte et panier. Ces panneaux sont des disclosures non modaux : navigation Tab habituelle, Échap avec retour au déclencheur, fermeture extérieure, fermeture après navigation et au changement de mode d’écran.
- La recherche conserve la catégorie actuelle du catalogue, respecte la normalisation de l’URL et possède une action d’effacement qui rend le focus au champ.
- Lorsqu’une fenêtre de panier/compte/checkout est ouverte, le fond est `inert`, le défilement du document est bloqué et le focus reste dans la fenêtre. Le passage panier → aperçu checkout déplace le focus dans le nouvel écran ; la fermeture le rend au déclencheur. Une navigation privilégie le focus du contenu principal.
- Aucun faux réseau social ni moyen de paiement actif n’est affiché. Le footer indique qu’aucun paiement réel n’est effectué dans la démonstration.

Vérification effectuée sur l’aperçu compilé : liens, recherche, clavier, ouverture/fermeture, conservation du panier, menu mobile et absence de débordement horizontal à 320, 390, 768, 1024 et 1440 px. Les tests de `navigation.test.js` contrôlent les destinations disponibles et les statuts en préparation.

## Accueil — point 3

`frontend/src/home.js` regroupe les collections, les destinations et les sélections de l’accueil. Les trois bannières utilisent les produits du catalogue pour leurs visuels, liens et prix. Leurs indicateurs ainsi que les boutons précédent/suivant sont manuels ; aucune rotation automatique. Le visuel et l’encart d’un produit ouvrent sa fiche, et le bouton de collection ouvre son univers dans le catalogue filtré.

Les offres sélectionnent les produits dont le prix est inférieur au prix de référence, puis les ordonnent par réduction. Les pourcentages sont arrondis vers le bas pour ne pas dépasser la réduction calculée. Le badge du carrousel affiche la réduction maximale de sa catégorie, et mène à `/promotions` avec ce filtre.

Les nouveautés utilisent `publishedAt` et une fenêtre de 30 jours, dans l’ordre des publications. L’instantané local `CATALOG_DATE` est fixé au **19 octobre 2026** ; ce n’est pas une actualisation commerciale en temps réel. Le serveur fournira ensuite les produits publiés et la référence temporelle adaptée. Les avis restent illustratifs et sont indiqués « avis démo » sur les cartes. Les portraits et étoiles suggérant des clients réels ont été retirés de la bannière.

Les routes `/promotions` et `/nouveautes` réutilisent la liste du catalogue : filtres de catégorie, recherche via l’URL, tri et états sans résultat. Elles sont fournies dès ce point pour que les liens d’accueil soient fonctionnels ; les filtres avancés, la pagination et les pages catégorie restent au point 4. Les paramètres acceptés restent bornés et les paramètres sensibles ou inconnus sont retirés par le routeur.

La bannière audio mène au high-tech et son lien porte ce libellé, sans promettre un filtre audio absent. Les services encore non connectés sont explicitement annoncés comme prévus ou simulés. Les liens de navigation sont de vrais liens internes ; favoris, ajout au panier et changements de bannière restent des boutons.

Vérifications : tests des dates, réductions, sélections et destinations ; compilation ; parcours navigateur avec liens directs, rechargement, historique, carrousel, filtres de collections, favoris, panier et largeurs de 320 à 1440 px. Aucune transaction réelle n’est effectuée.
