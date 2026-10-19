import { categories } from './shop.js'
import { makeCatalogQuery } from './router/policy.js'

export const categoryNavigation = categories.map(category => ({
  label: category.name,
  icon: category.icon,
  to: { name: 'catalog', query: makeCatalogQuery({ category: category.name }) },
}))

export const mainNavigation = [
  { label: 'Accueil', to: { name: 'home' } },
  { label: 'La boutique', to: { name: 'catalog' }, section: true },
  { label: 'Promotions', pending: true },
  { label: 'Nouveautés', pending: true },
]

export const footerNavigation = [
  { title: 'Explorer', items: [
    { label: 'Toute la boutique', to: { name: 'catalog' } },
    ...categoryNavigation.slice(1, 4),
    { label: 'Promotions', pending: true },
    { label: 'Nouveautés', pending: true },
  ] },
  { title: 'Votre NovaShop', items: [
    { label: 'Aperçu du compte', action: 'account' },
    { label: 'Mes favoris', to: { name: 'favorites' } },
    { label: 'Ouvrir mon panier', action: 'cart' },
    { label: 'Mes commandes', pending: true },
  ] },
  { title: 'Aide & NovaShop', items: [
    { label: 'À propos', pending: true },
    { label: 'Contact', pending: true },
    { label: 'Questions fréquentes', pending: true },
    { label: 'Livraison', pending: true },
    { label: 'Retours et remboursements', pending: true },
  ] },
  { title: 'Informations légales', items: [
    { label: 'Conditions de vente', pending: true },
    { label: 'Confidentialité', pending: true },
    { label: 'Mentions légales', pending: true },
    { label: 'Cookies', pending: true },
  ] },
]

export function isNavigationCurrent(route, item) {
  if (!item.to || route.name !== item.to.name) return false
  if (item.section) return true
  if (item.to.name === 'catalog') return (route.query.category || '') === (item.to.query?.category || '')
  return true
}
