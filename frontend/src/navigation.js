import { categories } from './shop.js'
import { categoryDestination } from './router/policy.js'

export const categoryNavigation = categories.map(category => ({
  label: category.name,
  icon: category.icon,
  to: categoryDestination(category.name),
}))

export const mainNavigation = [
  { label: 'Accueil', to: { name: 'home' } },
  { label: 'La boutique', to: { name: 'catalog' }, section: true },
  { label: 'Promotions', to: { name: 'promotions' } },
  { label: 'Nouveautés', to: { name: 'new-arrivals' } },
]

export const footerNavigation = [
  { title: 'Explorer', items: [
    { label: 'Toute la boutique', to: { name: 'catalog' } },
    ...categoryNavigation.slice(1, 4),
    { label: 'Promotions', to: { name: 'promotions' } },
    { label: 'Nouveautés', to: { name: 'new-arrivals' } },
  ] },
  { title: 'Votre NovaShop', items: [
    { label: 'Mon compte', to: { name: 'login' } },
    { label: 'Mes favoris', to: { name: 'favorites' } },
    { label: 'Mon panier', to: { name: 'cart' } },
    { label: 'Mes commandes', to: { name: 'account-orders' } },
  ] },
  { title: 'Aide & NovaShop', items: [
    { label: 'À propos', to: { name: 'about' } },
    { label: 'Contact', to: { name: 'contact' } },
    { label: 'Questions fréquentes', to: { name: 'help' } },
    { label: 'Livraison', to: { name: 'delivery' } },
    { label: 'Retours et remboursements', to: { name: 'returns-policy' } },
  ] },
  { title: 'Informations légales', items: [
    { label: 'Conditions de vente', to: { name: 'terms' } },
    { label: 'Confidentialité', to: { name: 'privacy' } },
    { label: 'Mentions légales', to: { name: 'legal' } },
    { label: 'Cookies', to: { name: 'cookies' } },
  ] },
]

export function isNavigationCurrent(route, item) {
  if (!item.to) return false
  if (item.section && item.to.name === 'catalog') return ['catalog', 'category'].includes(route.name)
  if (route.name !== item.to.name) return false
  if (item.to.name === 'category') return route.params?.slug === item.to.params.slug
  if (item.to.name === 'catalog') return (route.query.category || '') === (item.to.query?.category || '')
  return true
}
