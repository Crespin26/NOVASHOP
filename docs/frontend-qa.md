# Recette frontend — finalisation des lots 4, 5 et 6

## Exécution reproductible

Depuis la racine :

```bash
npm ci --prefix frontend
npm run check --prefix frontend
cd frontend
npx playwright install chromium
npm run test:e2e
```

La recette démarre le build de production avec Vite Preview sur `127.0.0.1:4191`. Reconstruire après chaque modification avant de lancer la recette. Le port doit être libre. Les tests sont isolés, sans backend ni données personnelles ; les requêtes d’images externes sont bloquées pour vérifier les états d’échec et éviter une dépendance réseau.

Pour un Chromium déjà installé :

```bash
PLAYWRIGHT_CHROMIUM_EXECUTABLE=/snap/bin/chromium npm run test:e2e
```

Les traces des échecs restent dans `frontend/test-results/` (ignoré par Git). La CI installe Chromium et exécute la même recette après les tests et le build ; les traces d’échec sont conservées comme artefact.

## Couverture

- Trois configurations : ordinateur français (1440 px), ordinateur anglais (1440 px), mobile anglais (390 px).
- Navigation sur les écrans publics et administratifs représentatifs, titre principal présent et absence de débordement horizontal.
- Changement FR/EN sans perte d’URL ou devise, préférence conservée au rechargement.
- Profil : validation par champ et focus ; adresses : création, erreurs, confirmation de retrait, annulation et restauration du focus.
- Galerie et images indisponibles ; panier et parcours en trois étapes ; reconnaissance de la simulation ; aucune requête de mutation métier ni conservation des coordonnées.
- Administration : pagination, création validée via le formulaire extrait et isolation par rapport au catalogue public.
- Clavier : menu mobile, Échap, boucle de tabulation du tiroir panier et restauration du focus.

## Changements de maintenance

La gestion administrative est extraite dans `useAdminWorkspace.js`, le formulaire produit dans `AdminProductEditor.vue` et la pagination dans `AdminPagination.vue`. Les listes produits, commandes, clients, retours, messages et journal utilisent la même pagination. La navigation indique aussi la section active sur les détails. Les confirmations d’ajustement de stock capturent les valeurs au moment de leur ouverture.

Les anciennes fenêtres compte et paiement d’`App.vue` ont été retirées. Les erreurs du profil et des adresses sont associées aux champs ; les modifications non enregistrées déclenchent une confirmation lors d’une navigation interne. Les données de ces écrans restent fictives et en mémoire.

## Limites à garder explicites

Cette recette ne certifie pas la conformité WCAG : les contrastes, lecteurs d’écran et appareils physiques restent à revoir dans la validation finale. Les services non connectés ne sont pas présentés comme opérationnels. Les caractéristiques, photos multiples et variantes commerciales restent à fournir. Les erreurs réseau métier, authentification, concurrence sur les stocks, paiements et autorisations réels seront testés après développement et connexion des API. Aucune mise en production n’est effectuée par ces commandes.

## Apparence claire et sombre

Le sélecteur soleil/lune précède la langue dans le bandeau et se retrouve dans l’administration. Le thème est enregistré dans `nova-preferences`, indépendamment de la langue et de la devise. Sans préférence valide, le mode clair est utilisé. Un script de démarrage applique le choix avant le montage Vue. Les photos ne sont pas inversées ; les surfaces, champs, menus, fenêtres et états de focus disposent de couleurs dédiées dans `src/theme.css`. La recette comprend persistance, indépendance des préférences et affichage ordinateur/mobile, avec captures des pages sombres. Cela ne remplace pas une revue de contraste exhaustive de chaque état.
