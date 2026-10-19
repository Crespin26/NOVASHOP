# Spécifications fonctionnelles

Ce document décrit le comportement cible de la plateforme complète. Un prototype Vue permet déjà de parcourir un catalogue fictif, rechercher, filtrer et gérer un panier local. Les garanties serveur décrites ici restent à implémenter.

## Utilisateurs

- **Visiteur :** consulter les produits, rechercher et constituer un panier.
- **Client authentifié :** payer, consulter ses commandes et suivre leur traitement.
- **Administrateur :** gérer le catalogue, les stocks et les commandes.

Le premier périmètre impose une connexion avant le paiement. Le panier visiteur est fusionné avec le panier du compte à la connexion, dans la limite du stock disponible.

## Catalogue et catégories

Un produit possède un nom, un identifiant public unique, une description, des images, un prix, une devise, un stock et un état de publication. Il appartient à une catégorie dans le premier périmètre. Un produit non publié n’apparaît pas dans le catalogue et ne peut pas être commandé.

La liste propose une recherche sur le nom et la description, des filtres par catégorie, prix et disponibilité, ainsi qu’un tri par nouveauté ou prix. Les résultats sont paginés. Les paramètres invalides provoquent une erreur de validation explicite.

Une catégorie utilisée ne peut pas être supprimée tant que ses produits n’ont pas été réaffectés.

## Panier

Le client peut ajouter un produit, modifier sa quantité et le retirer. Les quantités sont des entiers strictement positifs. Le serveur recalcule les prix et vérifie la disponibilité à chaque validation ; les montants envoyés par le navigateur ne font jamais autorité.

Le panier ne réserve pas de stock. Un changement de prix ou de disponibilité doit être présenté au client avant la création du paiement. Un panier vide ne peut pas être commandé.

## Commande et paiement

1. Le client vérifie son panier et renseigne une adresse de livraison.
2. Le serveur valide les produits, calcule les frais et le total, puis réserve le stock dans une transaction.
3. Une commande conserve un instantané des produits, prix et adresse.
4. Le serveur crée une session Stripe Checkout avec une clé d’idempotence liée à la tentative de paiement.
5. Le client est redirigé vers Stripe, puis vers une page de résultat.
6. Seul un webhook Stripe vérifié confirme le paiement ; la page de retour affiche l’état connu du serveur.

Un double clic ou une nouvelle tentative réseau ne doit créer ni double commande ni double réservation. Les réservations expirées sont libérées. Une session de paiement encore payable doit être expirée avant libération ; un rapprochement traite les éventuels paiements tardifs sans créer de stock négatif.

Le premier périmètre prévoit une seule devise et un tarif de livraison configurable, sans coupons, abonnements ni paiement fractionné. La devise, les pays desservis et les règles fiscales restent à définir avant mise en production.

## Suivi et administration

Le paiement et la livraison ont des états distincts :

- Paiement : `pending`, `paid`, `failed`, `expired`, `refunded`.
- Livraison : `unfulfilled`, `processing`, `shipped`, `delivered`, `cancelled`.

Une commande impayée ne peut pas être expédiée. Les transitions sont validées côté serveur et historisées avec leur auteur. Les remboursements partiels sont hors du premier périmètre ; un remboursement total nécessite une opération Stripe confirmée. Le réapprovisionnement après retour fait l’objet d’une décision explicite.

Le client voit exclusivement ses commandes. L’administrateur dispose de la gestion des produits et catégories, des ajustements de stock traçables et de la mise à jour du suivi. Le tableau de bord distingue chiffre d’affaires payé, remboursements, commandes à traiter et alertes de stock.

## Critères de livraison du premier périmètre

- Un client peut trouver un produit, l’ajouter au panier et effectuer un achat en mode test Stripe.
- Une confirmation de paiement répétée ne modifie les stocks qu’une seule fois.
- Deux achats concurrents du dernier article ne peuvent pas tous les deux réserver cet article.
- Un client ne peut pas consulter la commande d’un autre client ni accéder à l’administration.
- Une commande conserve ses montants et son adresse après modification du catalogue ou du profil.
- Les états vides, erreurs, chargements et ruptures de stock sont accessibles et compréhensibles sur mobile et ordinateur.
