import { informationPages } from '../information.js'
import { nextTick } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import { canonicalQuery, findProductBySlug, findCategoryBySlug, sameQuery } from './policy.js'

export const routes = [
  ...[...informationPages, {name:'help',path:'/aide',title:'Aide et FAQ'}, {name:'contact',path:'/contact',title:'Contact'}].map(page => ({path:page.path,name:page.name,component:()=>import('../views/InformationView.vue'),meta:{title:page.title}})),
  { path: '/commande', name: 'checkout', component: () => import('../views/CheckoutView.vue'), meta: { title: 'Votre commande' } },
  { path: '/commande/retour', name: 'checkout-return', component: () => import('../views/OrderView.vue'), meta: { title: 'Retour de paiement' } },
  { path: '/commande/paiement-interrompu', name: 'checkout-interrupted', component: () => import('../views/OrderView.vue'), meta: { title: 'Paiement interrompu' } },
  { path: '/commande/confirmation/:reference', name: 'checkout-confirmation', component: () => import('../views/OrderView.vue'), meta: { title: 'Confirmation de démonstration' } },
  { path: '/compte/commandes/:reference', name: 'account-order', component: () => import('../views/OrderView.vue'), meta: { title: 'Détail de démonstration' } },
  { path: '/compte/commandes/:reference/suivi', name: 'account-order-tracking', component: () => import('../views/OrderView.vue'), meta: { title: 'Suivi de démonstration' } },
  { path: '/compte/commandes/:reference/retour', name: 'account-order-return', component: () => import('../views/OrderView.vue'), meta: { title: 'Retour de démonstration' } },

  { path: '/connexion', name: 'login', component: () => import('../views/AuthView.vue'), meta: { title: 'Connexion' } },
  { path: '/inscription', name: 'register', component: () => import('../views/AuthView.vue'), meta: { title: 'Inscription' } },
  { path: '/mot-de-passe-oublie', name: 'forgot-password', component: () => import('../views/AuthView.vue'), meta: { title: 'Mot de passe oublié' } },
  { path: '/reinitialiser-mot-de-passe/:token', name: 'reset-password', component: () => import('../views/AuthView.vue'), meta: { title: 'Nouveau mot de passe' } },
  { path: '/verification-email', name: 'verify-email', component: () => import('../views/AccountView.vue'), meta: { title: 'Vérification e-mail' } },
  { path: '/compte', name: 'account', component: () => import('../views/AccountView.vue'), meta: { title: 'Mon compte' } },
  { path: '/compte/profil', name: 'account-profile', component: () => import('../views/AccountView.vue'), meta: { title: 'Mon profil' } },
  { path: '/compte/adresses', name: 'account-addresses', component: () => import('../views/AccountView.vue'), meta: { title: 'Mes adresses' } },
  { path: '/compte/commandes', name: 'account-orders', component: () => import('../views/AccountView.vue'), meta: { title: 'Mes commandes' } },

  { path: '/', name: 'home', component: () => import('../views/HomeView.vue'), meta: { title: 'Vos envies, un nouvel univers.' } },
  { path: '/boutique', name: 'catalog', component: () => import('../views/CatalogView.vue'), meta: { title: 'La boutique' } },
  { path: '/categories/:slug', name: 'category', component: () => import('../views/CatalogView.vue'), meta: { title: 'Catégorie' } },
  { path: '/promotions', name: 'promotions', component: () => import('../views/CatalogView.vue'), meta: { title: 'Les promotions' } },
  { path: '/nouveautes', name: 'new-arrivals', component: () => import('../views/CatalogView.vue'), meta: { title: 'Les nouveautés' } },
  { path: '/panier', name: 'cart', component: () => import('../views/CartView.vue'), meta: { title: 'Mon panier' } },
  { path: '/favoris', name: 'favorites', component: () => import('../views/CatalogView.vue'), meta: { title: 'Mes favoris' } },
  { path: '/produits/:slug', name: 'product', component: () => import('../views/ProductView.vue'), meta: { title: 'Produit' } },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../views/NotFoundView.vue'), meta: { title: 'Page introuvable' } },
]

export function createStoreRouter(history = createWebHistory()) {
  const router = createRouter({
    history,
    routes,
    scrollBehavior(to, from, savedPosition) {
      if (savedPosition) return savedPosition
      if (to.hash === '#main') return { el: '#main' }
      if (to.path === from.path && to.query.page !== from.query.page) return { el: '#catalog-results', top: 175 }
      return to.path === from.path ? false : { top: 0 }
    },
  })
  router.beforeEach(to => {
    const query = canonicalQuery(to.name, to.query)
    const hash = to.hash === '#main' ? '#main' : ''
    if (!sameQuery(to.query, query) || hash !== to.hash) return { path: to.path, query, hash, replace: true }
  })
  router.afterEach(async (to, from, failure) => {
    if (failure || typeof document === 'undefined') return
    const product = to.name === 'product' ? findProductBySlug(to.params.slug) : null
    const category = to.name === 'category' ? findCategoryBySlug(to.params.slug) : null
    const title = to.name === 'product' ? product?.name ?? 'Produit introuvable' : to.name === 'category' ? category?.name ?? 'Catégorie introuvable' : to.meta.title
    document.title = `${title} — NovaShop`
    if (to.path === from.path && to.query.page !== from.query.page) {
      await nextTick()
      document.getElementById('catalog-results')?.focus({ preventScroll: true })
    }
    if (to.path !== from.path) {
      await nextTick()
      document.getElementById('main')?.focus({ preventScroll: true })
    }
  })
  return router
}
