# Interface NovaShop

Le prototype Vue se trouve dans `frontend/`. Il reprend la structure des références fournies : en-tête, recherche, navigation par catégories, bannière de collection, offres, univers, nouveautés et pied de page.

## Direction visuelle

Une palette bleu nuit, corail et vert sauge, des titres Manrope et du texte DM Sans créent une identité distincte. Les photographies occupent une place importante ; les interactions ajoutent des zooms doux, des transitions, des favoris et des confirmations. Le mouvement est réduit si le système le demande.

La grille affiche quatre produits sur grand écran et deux sur mobile. La navigation et la bannière sont adaptées aux petits écrans. Les fenêtres de produit et le panier proposent une fermeture au clavier, un confinement du focus et des libellés accessibles.

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

Remplacer les données de `frontend/src/shop.js` par les réponses de l’API Laravel, puis connecter authentification, commandes et Stripe. Revalider les prix et stocks côté serveur. Les liens de catalogue et les vues actuelles utilisent un état Vue ; un routage avec URL partageables reste à ajouter.

Les images Unsplash et les polices Google sont chargées à distance. Avant publication commerciale, choisir et vérifier les droits des ressources finales, puis héberger les actifs validés localement.
